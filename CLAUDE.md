# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

LittleStep is an ECCD (Early Childhood Care and Development) progress-tracking system. It has two apps in one repo:

- `backend/` — Laravel 13 (PHP 8.3) app. It serves two audiences:
  - **Teachers** use the server-rendered Blade web UI (`routes/web.php`). They register guardians and children, record ECCD checklist assessments, write observations, and export PDF reports.
  - **Guardians** use the mobile app through a read-only JSON API (`routes/api.php`, prefixed `/api/v1`) authenticated with Sanctum bearer tokens.
- `mobile/` — Expo / React Native guardian app (TypeScript). It only talks to the `/api/v1/guardian/*` endpoints.

The dev environment is Laragon on Windows. The backend is served at `http://localhost/LittleStep/backend/public`, and the mobile app's base URL is built from that path.

### Official Documentation (must follow)

These files in `docs/` are the source of truth for domain rules and form layout:

- `docs/ECCD_Childs_Record_2_Summary.md` — Official Child's Record 2 form layout, fields, marking rules (✓ / —), score summary tables, and charts.
- `docs/ECCD_Technical_Administration_Manual_Summary.md` — Official scoring pipeline, age computation (30-day months), age bands, scaled/standard score tables, and interpretation bands.
- `docs/LittleStep_Capstone.md` — Product specification, scope, roles, and high-level design.

Always align new features and scoring logic with these documents.

## Commands

### Backend (run from `backend/`)

```bash
composer setup                      # install, copy .env, key:generate, migrate, npm install + build
composer dev                        # artisan serve + queue:listen + pail logs + vite, all at once
composer test                       # config:clear then artisan test
php artisan test --filter=test_verified_guardian_can_log_in   # single test method
php artisan test tests/Feature/ProgressObservationTest.php    # single file
php artisan migrate:fresh --seed    # rebuild DB with ECCD checklist + dev teacher/guardian/child data
vendor/bin/pint                     # format PHP (Laravel Pint)
npm run dev | npm run build         # Vite + Tailwind v4 assets
```

Tests run on in-memory SQLite (see `phpunit.xml`) with `RefreshDatabase`. `.env.example` defaults to SQLite, but a Laragon `.env` typically uses MySQL. With `CACHE_STORE`/`SESSION_DRIVER=database`, artisan commands such as `optimize:clear` fail if MySQL is not running.

### Mobile (run from `mobile/`)

```bash
npm install
npx expo start          # or npm run android / ios / web
npm run lint            # expo lint (ESLint, eslint-config-expo)
npx tsc --noEmit        # type-check (no script defined for this)
```

There are no mobile tests. Copy `.env.example` to `.env` and set `EXPO_PUBLIC_API_URL` to the Laravel **public** URL, without `/api`, that the device can reach. Use your LAN IP for Expo Go, or `http://10.0.2.2/...` for the Android emulator. In `__DEV__` the app falls back to the Expo host IP plus `/LittleStep/backend/public`. Outside dev it requires HTTPS.

## Backend architecture

### Auth and roles

- There is a single `users` table with a `role` column (`teacher` | `guardian`). Profile data lives in `teachers` or `guardians` through a `hasOne` relation (`$user->teacher`, `$user->guardian`).
- Middleware aliases are set in `bootstrap/app.php`: `auth` → `ApiAuthenticate` (redirects web requests to login and returns 401 JSON for API requests), `teacher`, and `guardian`. `GuardianMiddleware` also requires a guardian profile and a verified email.
- Web routes use session auth plus `['auth', 'verified', 'teacher']`. API routes use `['auth:sanctum', 'guardian', 'verified']`. Guardian tokens are issued by `Api\V1\AuthController::login` with a 12-hour expiry.
- All route files are registered in `withRouting()` in `bootstrap/app.php`. `routes/api.php` gets the `api` prefix and middleware group from there.
- Guardians are created by teachers (`GuardianController::store`) and verify their email through `CustomVerifyEmail`. Teachers self-register at `/auth/register`. Seeded dev users are not email-verified.

### Data ownership (important for any query)

Teachers do not own children directly. A teacher "owns" a child through `progress_records.teacher_id`. Always scope teacher queries through `Teacher::ownedChildren()` / `Teacher::ownedGuardians()`, which include soft-deleted progress records. Do not use a raw `Child::find()`. Guardian API queries are scoped through `$request->user()->guardian->children()`. Tests in `tests/Feature/` check these cross-tenant boundaries.

### ECCD assessment model

Everything is defined in one migration, `database/migrations/2026_05_26_101334_all_tables.php`. Most tables use soft deletes, and "archive" in the UI means soft delete (`ArchiveController` restores records).

- `eccd_checklist_items` is the static checklist across 7 domains (`gross_motor`, `fine_motor`, `self_help`, `receptive_language`, `expressive_language`, `cognitive`, `social_emotional`). It is seeded by `ECCDChecklistSeeder`.
- `progress_records` has one row per child (`child_id` is unique) and up to 3 evaluation rounds (`evaluation_date_round_{1,2,3}`).
- `domains` holds one row per checklist item per round, and `domain_results` stores whether the item was present (`check` or `hyphen`). `domain_observations` holds one row of free-text notes per progress record.
- `domain_scores` stores the computed raw, scaled, and standard scores plus an interpretation code.

`App\Services\EccdExpertSystem` is the scoring engine. It computes the child's age at the test date, picks an age band, counts raw scores per domain, converts them through hard-coded scaled-score tables, then calculates the standard score and the interpretation. `saveScores()` writes the results to `domain_scores`. `ProgressController` (web) and `Api\V1\GuardianController::dashboard` both call it. The PDF report (`barryvdh/laravel-dompdf`, view `progress/pdf/report`) uses it too.

### Cross-cutting conventions

- **Audit log**: call the global helper `recordLog($action, $entityType, $entityId, $details)` (in `app/helpers.php`, autoloaded via composer `files`) after teacher actions such as create, update, view, download, and archive. Results appear on `/logs`.
- **CSP**: `SecurityHeaders` middleware sets a strict nonce-based Content-Security-Policy on web and api responses. Any inline `<script>`/`<style>` in Blade must include `nonce="{{ request()->attributes->get('cspNonce') }}"`. External styles and fonts are allowed only from cdnjs.cloudflare.com and Google Fonts. In production, HTTP requests are redirected to HTTPS.
- **Child photos** are stored on the private `local` disk (`photos/children`). They are never public. They are served through authenticated, ownership-checked routes: `children.photo` (teacher) and `/api/v1/guardian/children/{id}/photo` (guardian).
- Auth form POSTs are throttled (`throttle:5,1`).

## Mobile architecture

- The entry point is `App.tsx`, registered via `expo/AppEntry.js`. It uses a single React Navigation **stack** navigator and does **not** use expo-router or an `app/` directory. New screens must be registered in `App.tsx`. On launch it calls `restoreGuardianSession()` to pick the initial route (`Welcome` / `Login` / `SelectStudent`).
- `services/guardianApi.ts` is the only API layer. It resolves the base URL, attaches the bearer token, applies a 15 s timeout, validates every response with runtime type guards, and wraps errors in `GuardianApiError`. A 401 or 403 clears the stored token. Tokens persist in `expo-secure-store` only when "remember me" is checked, and are kept in memory otherwise. Web has no persistent sessions. Child photos must point back to the trusted API origin. They are fetched with the token and handed to `<Image>` as a `data:` URI, never as a URL with auth headers.
- Display mode (light / dark / system) comes from `useAppTheme()` in `hooks/use-app-theme.tsx`, provided in `App.tsx`. Screens keep their original light colors and layer `DarkColors` (`constants/theme.ts`) styles when `isDark` is true (`[styles.x, isDark && darkStyles.x]`, with a `darkStyles` StyleSheet at the bottom of each screen). Every screen follows this pattern, and new screens must too.
- Screens live flat in `screens/`. Apart from `use-app-theme.tsx` and `DarkColors`, `components/`, `hooks/`, and `constants/theme.ts` are mostly leftover Expo template scaffolding.
- The app is on Expo SDK ~57 (React Native 0.86, React 19.2). `mobile/AGENTS.md` (imported by `mobile/CLAUDE.md`) says to read the versioned docs at https://docs.expo.dev/versions/v57.0.0/ before writing Expo code. Update that link when upgrading the SDK.

## Mandatory Documentation Updates

Whenever you implement a **new feature** or make a **significant change** to existing functionality:

1. Update the relevant `README.md`:
   - `backend/README.md` — for any backend / web / API / expert-system changes
   - `mobile/README.md` — for any mobile app changes
2. Keep the README concise but useful: describe what the feature does, how to use it, and any new commands or configuration.
3. If the change affects scoring, forms, or data model, also verify alignment with the three docs in the `docs/` folder.

## Response style

- Prefer minimal, surgical changes that respect existing patterns.
- Always respect data ownership scoping and soft deletes.
- When touching scoring logic, follow `EccdExpertSystem` and the official 30-day month + age-band rules.
- After backend changes that affect teachers or guardians, mention relevant tests or suggest running them.
- Keep visual/data contracts close to Child's Record 2 layout.
- Never invent new domains, age bands, or interpretation codes.
