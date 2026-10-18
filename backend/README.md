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

**Archiving.** Most models use soft deletes. "Archive" in the UI soft-deletes a record, and `/archive` restores it.

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
