# LittleStep — Backend

Laravel 13 (PHP 8.3) backend for LittleStep, an ECCD (Early Childhood Care and Development) progress-tracking system.

It serves two audiences:

| Audience  | Interface                          | Auth                         | Routes            |
|-----------|------------------------------------|------------------------------|-------------------|
| Teachers  | Server-rendered Blade web app      | Session + verified email     | `routes/web.php`  |
| Guardians | JSON API used by the `mobile/` app | Sanctum bearer token (12 h)  | `routes/api.php` → `/api/v1/...` |

Teachers register guardians and children, record ECCD checklist assessments over up to three evaluation rounds, write observation notes, and download PDF progress reports. Guardians have read-only access to their own children's profiles and progress.

## Requirements

- PHP 8.3+ and Composer
- Node.js + npm (Vite / Tailwind CSS v4)
- MySQL (Laragon) or SQLite

## Setup

```bash
cd backend
composer setup
```

`composer setup` installs PHP and JS dependencies, creates `.env` from `.env.example`, generates the app key, runs migrations, and builds front-end assets.

Next, edit `.env` for your environment:

- **Database:** `.env.example` defaults to SQLite (`database/database.sqlite`). For Laragon MySQL, set `DB_CONNECTION=mysql` and fill in the `DB_*` values.
- **Mail:** `MAIL_MAILER=log` writes emails, including verification links, to `storage/logs/laravel.log`. Configure SMTP to send real mail.
- **`SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`:** these need the database to be reachable.

Seed the ECCD checklist and the development data:

```bash
php artisan migrate:fresh --seed
```

`DatabaseSeeder` always seeds the ECCD checklist items. Outside production, it also creates a sample teacher, guardian, child, and progress record. See `database/seeders/DatabaseSeeder.php` for the accounts. Seeded users are **not** email-verified, and both the web dashboard and the guardian API require a verified email. Verify them through the emailed link or by setting `email_verified_at` (e.g. in `php artisan tinker`).

## Running

With Laragon, the app is served at `http://localhost/LittleStep/backend/public`. Run `npm run dev` for hot-reloaded assets, or `npm run build` once.

Without Laragon:

```bash
composer dev   # artisan serve + queue worker + log tail (pail) + Vite, together
```

## Testing and formatting

```bash
composer test                                            # full suite
php artisan test tests/Feature/ProgressObservationTest.php
php artisan test --filter=test_guardian_mobile_login_requires_email_verification
vendor/bin/pint                                          # format PHP
```

Tests use in-memory SQLite (configured in `phpunit.xml`), so no database server is needed.

## Project structure

```
app/
  Http/Controllers/          Teacher web controllers (Guardian, Children, Progress, Archive, Logs, ...)
  Http/Controllers/Api/V1/   Guardian mobile API (AuthController, GuardianController)
  Http/Middleware/           ApiAuthenticate, TeacherMiddleware, GuardianMiddleware, SecurityHeaders
  Models/                    User, Teacher, Guardian, Child, ProgressRecord, Domain*, EccdChecklistItem, Log
  Services/EccdExpertSystem.php   ECCD scoring engine
  helpers.php                recordLog() audit helper (autoloaded)
database/migrations/         Single migration defining all tables
resources/views/             Blade views, incl. progress/pdf/report (DomPDF)
```

## Key concepts

**Users and roles.** There is one `users` table with a `role` of `teacher` or `guardian`. Profile details live in `teachers` / `guardians`. The `teacher` and `guardian` middleware aliases are registered in `bootstrap/app.php`. Teachers self-register at `/auth/register`. Guardian accounts are created by teachers.

**Ownership.** A teacher owns a child through that child's progress record (`progress_records.teacher_id`). Scope teacher queries with `Teacher::ownedChildren()` / `ownedGuardians()`. Scope guardian API queries with `$user->guardian->children()`.

**ECCD scoring.** `EccdExpertSystem` takes the checklist results for a round across 7 domains (gross motor, fine motor, self-help, receptive language, expressive language, cognitive, social-emotional). From the child's age at the test date it computes raw, scaled, and standard scores and an interpretation, then stores them in `domain_scores`.

**Archiving.** Most models use soft deletes. "Archive" in the UI soft-deletes a record, and `/archive` restores it. The Archives page has Guardians and Children tabs: `?tab=children` opens the children list. Pagination links and child restores keep the right tab open.

**Success and error messages.** All web pages use the same two Blade components, so messages look and behave the same everywhere:

- `<x-flash-messages />` is rendered once by both layouts (`components.app`, `components.auth`). Controllers flash `success` (or Laravel's `status`) and `error`, e.g. `redirect()->route('children.index')->with('success', 'Child archived successfully.')`. It shows a dismissible green or red banner with the right screen-reader role. Pages must not render these session keys themselves.
- `<x-validation-errors />` shows a red summary of `$errors` at the top of a form. It can sit next to inline `@error` messages under fields. When validation fails, the form stays visible with the user's input kept.
- Every create, update, archive and restore on the Guardians and Children pages is a normal form submit. The controller redirects with a flash message, so the banner looks the same on every page. Confirmations use `data-confirm="..."` on the `<form>`; the layouts handle it. There are no per-page result panels, AJAX result screens or browser `alert()` messages.

| Action | Banner shown on |
|--------|-----------------|
| Register guardian + child, edit guardian, archive guardian, link child | Guardians list |
| Archive a linked child (Guardians → Unlink Child) | Unlink Child page, or the Guardians list if that was the guardian's last active child |
| Edit child | Child profile |
| Archive child | Children list |
| Restore guardian / child | Archives (Guardians / Children tab) |

`tests/Feature/FlashMessagesTest.php` follows each main action through its redirects and checks that its message appears exactly once on the page the teacher lands on. Run `npm run build` after changing these components so Tailwind picks up their classes.

**Forms and validation.** The teacher forms share their rules through `App\Support\FormRules`, so a field is accepted or rejected the same way everywhere:

- **Names** (people, parents, relationship): letters including ñ and accents, spaces, periods, apostrophes and hyphens, up to 255 characters, e.g. `Ma. Peña-O'Neil`.
- **Contact numbers** (teacher and guardian): 11 digits starting with `09`.
- **Passwords:** 12–72 characters.
- **Child profile:**
  - The date of birth can't be in the future.
  - Handedness is required.
  - Parents' age must be 10–120, siblings 0–30, birth order 1–31.
  - Parents' education comes from a fixed list (`<x-education-select>`). An older stored value stays selectable when editing.
  - The photo must be a JPG, PNG or GIF of at most 2 MB, stored under `photos/children`.
- **Browser hints:** inputs carry matching `maxlength`/`min`/`max`/`pattern` attributes, but the server rules are the authority.
- **Double submits:** the teacher layout blocks double submits of POST forms, so a double-click can't create duplicates.
- **Progress comments:** a progress comment needs a ✓/— response before it can be saved. This applies on both the create and edit forms.

**Default guardian password.** On Guardians → Add Guardian the password is optional. If both password fields are left empty, the account gets the default password from `GUARDIAN_DEFAULT_PASSWORD` (`Password123@` when unset, see `config/littlestep.php`). The form shows the default, and the success banner says when it was used. Everyone who gets the default shares the same known password. The guardian should change it from "Forgot password" after the first sign-in. For real deployments, set your own value in `.env`.

**Changing a guardian's email.** Saving a new email marks the account unverified, signs the guardian out of the mobile app, and sends a verification link to the new address. This also fixes typos in the original registration email.

**Audit log.** Teacher actions call `recordLog($action, $entityType, $entityId, $details)`. Entries are listed at `/logs`.

**Security.**
- `SecurityHeaders` adds a nonce-based Content-Security-Policy, so inline `<script>`/`<style>` tags need `nonce="{{ request()->attributes->get('cspNonce') }}"`. In production it also redirects to HTTPS and sends HSTS.
- Login, registration, and password-reset POSTs are throttled to 5 per minute.
- Child photos are stored on the private `local` disk and served only through authenticated, ownership-checked routes.

## Guardian API (v1)

All endpoints are under `/api/v1`. Everything except login requires `Authorization: Bearer <token>` and a verified guardian account.

| Method | Path                                      | Description                              |
|--------|-------------------------------------------|------------------------------------------|
| POST   | `/guardian/login`                         | Returns `{ token, guardian, expires_at }` |
| POST   | `/guardian/logout`                        | Revokes the current token                |
| GET    | `/guardian/profile`                       | Guardian's name, account email, sex, contact number, relationship to child and address (barangay/municipality/province/region are stored joined in `address`) |
| GET    | `/guardian/contacts`                      | Each of the guardian's children with the teacher handling them (name and contact number only; `null` until a teacher starts a progress record) |
| GET    | `/guardian/views`                         | Guardian with children, progress records, domains and scores |
| GET    | `/guardian/children`                      | Guardian's children                      |
| GET    | `/guardian/children/{id}`                 | Child details: name, sex, birthday, address, handedness, schooling, parents' name/age/occupation/education, siblings and birth order, and `photo_url` (explicit fields only, no storage paths) |
| GET    | `/guardian/children/{id}/dashboard`       | Progress summary, raw/scaled scores for all 7 domains per round, and `recent_activity` (domains ordered by when the teacher last updated them, newest first) |
| GET    | `/guardian/children/{id}/domains/{domain}`| One domain's checklist: each item's ✓/— result and teacher comment per round, plus raw/scaled score and assessed-item count per round. `{domain}` is one of the 7 domain keys (`gross_motor`, `fine_motor`, `self_help`, `receptive_language`, `expressive_language`, `cognitive`, `social_emotional`); anything else returns 404 |
| GET    | `/guardian/children/{id}/photo`           | Child photo (authenticated image)        |
