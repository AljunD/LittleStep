# LittleStep

**A Learner Progress Monitoring System for Barangay Balite Day Care Center**

LittleStep replaces handwritten ECCD (Early Childhood Care and Development) records with a web and mobile system. Teachers record checklist evaluations, a rule-based expert system computes the official Philippine ECCD Checklist scores, and guardians follow their child's progress on their phone.

Undergraduate capstone project (BS Information Technology), New Era University — College of Informatics and Computing Studies, 2026.
Authors: Aljun B. Dalman, Christoper Jul P. Francisco, Michelle P. Llander.

## Features

| | Teacher web portal | Guardian mobile app |
|---|---|---|
| **Accounts** | Register, verify email, sign in | Sign in with a teacher-created, email-verified account; optional secure "Remember me" |
| **Children & guardians** | Register, edit, archive and restore guardians and children | View your own children only |
| **ECCD checklist** | Record ✓ / — results across all 7 domains for up to 3 evaluation rounds, plus observation notes | View progress, domain scores and interpretation per round |
| **Scoring** | Automatic age, raw, scaled and standard scores, and interpretation | Same results, read-only |
| **Reports** | Child's Record 2–style PDF progress report | Dashboard summary |
| **Other** | Audit log of teacher actions | Light / dark / system display mode, responsive on phones, tablets and web |

### ECCD expert system

Scoring follows the official ECCD Technical Manual. It is deterministic and rule-based, not machine learning:

1. **Age at test date:** each month counts as 30 days, with no rounding.
2. **Raw score:** the number of ✓ items in each of the 7 domains (gross motor, fine motor, self-help, receptive language, expressive language, cognitive, social-emotional).
3. **Scaled score:** looked up per domain and age band.
4. **Sum of scaled scores, then standard score:** the 7 scaled scores are added and the total is converted to a standard score.
5. **Interpretation:** the standard score is mapped to the official developmental bands.

## Repository structure

```
backend/   Laravel 13 (PHP 8.3) — teacher web portal, guardian JSON API, ECCD expert system
mobile/    Expo / React Native (TypeScript) — guardian app for Android, iOS and web
```

| Layer | Technology |
|---|---|
| Teacher portal | Laravel Blade, Tailwind CSS, DomPDF |
| API | Laravel REST API (`/api/v1`) with Sanctum bearer tokens |
| Mobile | Expo SDK 58, React Native, React Navigation |
| Database | MySQL (SQLite for local development and tests) |

## Getting started

Each app has its own setup guide:

- **[Backend setup →](backend/README.md)**: install, configure `.env`, migrate and seed, run tests, plus the guardian API reference.
- **[Mobile setup →](mobile/README.md)**: install, point `EXPO_PUBLIC_API_URL` at the backend, run on a device, emulator or the web.

Quick start:

```bash
# Backend
cd backend
composer setup
php artisan migrate:fresh --seed
composer dev

# Mobile (in a second terminal)
cd mobile
npm install
cp .env.example .env    # then set EXPO_PUBLIC_API_URL
npx expo start
```

## Security

- Role-based access: teachers and guardians only ever see the children linked to them.
- Login, registration and password reset are rate-limited.
- Guardian accounts require email verification.
- Child photos are stored privately and served only to the authenticated teacher or guardian.
- The web portal sends a strict Content-Security-Policy.
- Secrets (`.env`), databases and uploaded photos are excluded from the repository by `.gitignore`.
