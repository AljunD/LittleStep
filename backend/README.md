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
resources/pdf/childs-record-2/  Official Child's Record 2 pages used as the PDF background
```

## Key concepts

**Users and roles.** There is one `users` table with a `role` of `teacher` or `guardian`. Profile details live in `teachers` / `guardians`. The `teacher` and `guardian` middleware aliases are registered in `bootstrap/app.php`. Teachers self-register at `/auth/register`. Guardian accounts are created by teachers.

**Ownership.** A teacher owns a child through that child's progress record (`progress_records.teacher_id`). Scope teacher queries with `Teacher::ownedChildren()` / `ownedGuardians()`. Scope guardian API queries with `$user->guardian->children()`.

**ECCD scoring.** `EccdExpertSystem` takes the checklist results for a round across 7 domains (gross motor, fine motor, self-help, receptive language, expressive language, cognitive, social-emotional). From the child's age at the test date it computes raw, scaled, and standard scores and an interpretation, then stores them in `domain_scores`.

**Interactive expert system.** The same engine also explains and previews its results for both teachers and guardians. The scoring rules, age bands and interpretation codes are unchanged.

- **The explanation:** `explain()` / `explainRound()` give a step-by-step account of a round:
  - age in 30-day months, then the age band
  - each domain's answered items, raw score, scaled score and Table 6 interpretation
  - the sum of scaled scores and the standard score
  - warnings, e.g. unanswered items, or an age outside 3y1m–5y11m (which leaves the scaled scores blank)
- **Where it shows:**
  - **Teachers:** the progress record page has a **How Was This Computed?** section for every round with a date.
  - **Guardians:** `GET /guardian/children/{id}/insights`, shown on the mobile **Why This Result?** screen.
- **Skills to practise:** `skillsToPractice()` lists the official checklist items marked — in the latest round of each domain.
  - **Teachers:** shown as **Focus Items for the Next Round**.
  - **Guardians:** shown as "Things to try at home".
- **Live preview:** while a teacher marks ✓/— on the evaluation form, `POST /progress/preview` re-scores the page without saving anything.
  - The page shows the domain's answered items, raw score, scaled score and interpretation, the standard score once all 7 domains are scored, and the warnings.
  - The preview goes through `computeFromAnswers()`, the same code that scores saved results, so it always matches what saving produces.
  - The route is teacher-only, scoped to the teacher's own children, and limited to 120 requests per minute.
  - New Tailwind classes need `npm run build`.

Domain interpretations follow the Technical Manual's Table 6 wording, e.g. "Suggest slight delay" or "Average development".

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

**Buttons and action menus.** Every button in the teacher portal and on the sign-in pages comes from a shared Blade component, so all buttons are the same size and style. Each is 40px tall with the same corners, font and keyboard focus ring.

- **`<x-button>`:** a button, or a link when it has `href`. Pass `type="submit"` inside forms.
  - Variants: `primary` (save or main action), `secondary` (cancel, back, neutral) and `danger`.
  - An optional `icon` adds one of the icons from `<x-icon>`.
- **`<x-action-menu>`:** a ⋮ button that keeps a row's or record's actions out of sight until it is clicked. The menu holds `<x-action-menu.item>` links, or submit items placed inside their form (archive/restore forms keep `data-confirm`).
  - **Where it's used:** list rows on Guardians, Children, Progress Records and Archives, plus the Unlink Child page, and the record actions on the guardian profile, child profile and progress record pages.
  - **How it behaves:** the layout script opens one menu at a time next to its button, so tables that scroll sideways can't clip it. It follows the button when the page scrolls and closes on an outside click, Escape or Tab. Arrow keys move through the items.
- **`<x-pagination-buttons :paginator>`:** the Previous/Next buttons.

Form actions (Save/Cancel, wizard steps, Filter, Add Guardian) stay visible. Run `npm run build` after adding new button classes.

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

**Default guardian password.** On Guardians → Add Guardian the password is optional. If both password fields are left empty, the account gets the default password from `GUARDIAN_DEFAULT_PASSWORD` (`Password123@` when unset, see `config/littlestep.php`). The form shows the default, and the success banner says when it was used. Everyone who gets the default shares the same known password. When a guardian signs in to the mobile app with it, the login response sets `must_change_password`. The app then reminds them to change it in Profile → Change Password (`PUT /api/v1/guardian/password`). For real deployments, set your own value in `.env`.

**Changing a guardian's email.** Saving a new email marks the account unverified, signs the guardian out of the mobile app, and sends a verification link to the new address. This also fixes typos in the original registration email.

**Evaluation status.** The Progress Records list and the progress record page both use `<x-progress-status>`:

- **The evaluation shown:** the highest round with any answers.
- **The status:** green Completed (all 7 domains), amber In Progress, or gray Pending, with "n/7 domains completed".
- **The date:** that round's assessment date.

Both pages get this from `ProgressController::currentRoundProgress()`, so they always agree.

**Editing a progress record.** The edit form (`/progress/edit/{id}`) covers the assessment dates, every round's ✓/— marks and comments, and the observation notes. Teachers reach it in three ways:

- **Edit Record** next to **Download PDF Report** at the top of the progress record page.
- **Edit** next to **View** on each row of the Progress Records list. The row buttons are always visible, so they also work on touch screens and with the keyboard.
- A small **Edit** link on each section of the record page (Computation of the Child's Age, each domain table, Notes). It opens the form at the matching section: `#assessment-dates`, `#domain-{domain_key}` or `#observation-notes`.

**PDF download (Child's Record 2).** On a progress record, **Download PDF Report** (`/progress/{id}/download-pdf`) produces the official ECCD Checklist, Child's Record 2: 20 US Letter pages, filled in.

- **How it's built:**
  - Every page uses the official form as its background (`resources/pdf/childs-record-2/page-01.jpg` … `page-20.jpg`, rendered at 150 dpi from the official Child's Record 2 PDF).
  - `App\Services\ChildsRecord2Report` places the record's data at positions measured on that form, in PDF points.
  - The view `progress/pdf/report` only paints what the service returns.
- **What gets filled in, page by page:**
  - **Page 1:** the Sociodemographic Profile, with the handedness and studying boxes ticked.
  - **Page 2:** date tested, date of birth and the age in 30-day months for each assessment, plus the examiner.
  - **Pages 4–16:** ✔ / — in the 1st/2nd/3rd Eval columns of every checklist item, the teacher's comments, and each domain's TOTAL SCORE.
  - **Page 17:** the examiner's notes, written on the ruled lines.
  - **Page 18:** raw and scaled scores, sum, standard score and interpretation.
  - **Pages 19–20:** the scaled-score and standard-score charts, with each score marked × and the marks connected.
- **Changing the layout:** the measured positions are constants at the top of the service. If the template images are ever replaced, re-measure them.
- **File size:** a download is about 4 MB.
- **Ownership:** only the record's teacher can download it.

**Audit log.** Teacher actions call `recordLog($action, $entityType, $entityId, $details)`. Entries are listed at `/logs`.

**Security.**
- `SecurityHeaders` adds a nonce-based Content-Security-Policy, so inline `<script>`/`<style>` tags need `nonce="{{ request()->attributes->get('cspNonce') }}"`. In production it also redirects to HTTPS and sends HSTS.
- Login, registration, and password-reset POSTs are throttled to 5 per minute.
- Child photos are stored on the private `local` disk and served only through authenticated, ownership-checked routes.

## Guardian API (v1)

All endpoints are under `/api/v1`. Everything except login requires `Authorization: Bearer <token>` and a verified guardian account.

| Method | Path                                      | Description                              |
|--------|-------------------------------------------|------------------------------------------|
| POST   | `/guardian/login`                         | Returns `{ token, guardian, expires_at, must_change_password }`. `must_change_password` is true when the guardian signed in with the default password (`GUARDIAN_DEFAULT_PASSWORD`) |
| POST   | `/guardian/logout`                        | Revokes the current token                |
| PUT    | `/guardian/password`                      | Change password. Body: `current_password`, `password`, `password_confirmation`. Same rules as the web forms (12–72 characters), and the new password must differ from the current one and from the default. Wrong current password → 422 on `current_password`. On success, signs out the guardian's other devices (this token stays valid) and logs `password_changed`. Throttled to 5 per minute |
| GET    | `/guardian/profile`                       | Guardian's name, account email, sex, contact number, relationship to child and address (barangay/municipality/province/region are stored joined in `address`) |
| GET    | `/guardian/contacts`                      | Each of the guardian's children with the teacher handling them (name and contact number only; `null` until a teacher starts a progress record) |
| GET    | `/guardian/views`                         | Guardian with children, progress records, domains and scores |
| GET    | `/guardian/children`                      | Guardian's children                      |
| GET    | `/guardian/children/{id}`                 | Child details: name, sex, birthday, address, handedness, schooling, parents' name/age/occupation/education, siblings and birth order, and `photo_url` (explicit fields only, no storage paths) |
| GET    | `/guardian/children/{id}/dashboard`       | Progress summary, raw/scaled scores for all 7 domains per round, and `recent_activity` (domains ordered by when the teacher last updated them, newest first) |
| GET    | `/guardian/children/{id}/domains/{domain}`| One domain's checklist: each item's ✓/— result and teacher comment per round, plus raw/scaled score, scaled-score `interpretation` and assessed-item count per round, and `skills_to_practice` (items marked — in the latest round, or `null`). `{domain}` is one of the 7 domain keys (`gross_motor`, `fine_motor`, `self_help`, `receptive_language`, `expressive_language`, `cognitive`, `social_emotional`); anything else returns 404 |
| GET    | `/guardian/children/{id}/photo`           | Child photo (authenticated image)        |
| GET    | `/guardian/children/{id}/insights`        | "Why this result?": for every round with an assessment date, the expert system's explanation (age and band, per-domain answered/raw/scaled/interpretation, sum, standard score, steps, warnings), plus `skills_to_practice` per domain. Empty `rounds` until a progress record exists |
