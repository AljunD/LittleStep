# LittleStep — Mobile (Guardian App)

Expo / React Native app (TypeScript) that guardians use to view their children's ECCD progress. It is a client of the LittleStep Laravel backend's guardian API (`/api/v1/guardian/*`). See [`../backend/README.md`](../backend/README.md).

**Stack:** Expo SDK 58, React Native 0.88, React 19, React Navigation (stack), `expo-secure-store`.

## Setup

1. Start the backend and make sure it is reachable from your device or emulator. For Laragon, that is `http://<host>/LittleStep/backend/public`.
2. Install dependencies:

   ```bash
   cd mobile
   npm install
   ```

3. Create `.env` from `.env.example` and set the backend's **public** URL. Do not add `/api`:

   ```bash
   # Physical device with Expo Go: use your computer's LAN IP
   EXPO_PUBLIC_API_URL=http://192.168.x.x/LittleStep/backend/public
   # Android emulator:
   # EXPO_PUBLIC_API_URL=http://10.0.2.2/LittleStep/backend/public
   ```

   In development, if this variable is unset, the app falls back to the Expo dev-server host plus `/LittleStep/backend/public`. Outside development, the URL must use HTTPS. Restart Expo after changing `.env`.

4. Start the app:

   ```bash
   npx expo start      # then press a (Android), i (iOS), w (web), or scan the QR code with Expo Go
   ```

To sign in you need a guardian account created by a teacher in the web app, with a **verified** email.

## Scripts

| Command           | Description                    |
|-------------------|--------------------------------|
| `npm start`       | Start the Expo dev server      |
| `npm run android` | Start and open on Android      |
| `npm run ios`     | Start and open on iOS          |
| `npm run web`     | Start and open in the browser  |
| `npm run lint`    | Run ESLint (`expo lint`)       |
| `npx tsc --noEmit`| Type-check                     |

## Release builds and Google Play

- **App identity:** set in `app.json`. The name is **LittleStep**, and the Android package and iOS bundle ID are **`ph.littlestep.guardian`** (permanent once uploaded to Google Play). The deep-link scheme is `littlestep`.
- **Icons:** in `assets/images/` (app icon, Android adaptive and monochrome layers, splash, web favicon). They're made from the LittleStep mark; replace them with a higher-resolution logo when one is available.
- **Builds:** EAS Build profiles are in `eas.json`. `preview` makes an installable **APK** for testers; `production` makes the **AAB** for Google Play and raises the version code automatically.
  - Both read `EXPO_PUBLIC_API_URL` from EAS environment variables (`eas env:create`), not from `.env`, so a build always points at the live **https** server.
  - A build without it shows "not connected to the server" instead of reaching a wrong address.
  - The project is linked to the Expo account **peinzein** (`owner` and `extra.eas.projectId` in `app.json`; builds at https://expo.dev/accounts/peinzein/projects/littlestep). `EXPO_PUBLIC_API_URL` is set to `https://littlestep.wasmer.app` for both profiles, and the Android signing keystore is stored by Expo.
  - Build a tester APK: `eas login`, then `eas build -p android --profile preview`. When it finishes, the build page has the APK download link and a QR code to install it.
- **iPhone (web app):** iOS can't install the APK, and the App Store needs a paid Apple Developer account. Instead, iPhone users open **https://littlestep.wasmer.app/app** in Safari and tap **Share → Add to Home Screen**. It opens full screen with the LittleStep icon. Like any web session, guardians sign in each time (no Remember me).
  - Every Wasmer deploy builds it (`backend/deploy/wasmer/build.sh` runs `expo export --platform web`). `experiments.baseUrl` in `app.json` (`/app`) sets the path, so `npm run web` also opens at `/app` locally.
  - `public/` holds the web page template (`index.html`: home-screen tags and a Content-Security-Policy that only allows the app's own origin), `manifest.json` and the home-screen icons made from `assets/images/icon.png`.
- **Privacy policy:** shown in the browser from the Login screen and Profile → Privacy Policy (`services/privacy-policy.ts`, which opens `<server>/privacy`).
- **Publishing:** see [`PLAY_STORE.md`](PLAY_STORE.md) for the full checklist: one-time setup commands, closed testing (12 testers for 14 days on new personal accounts), the demo account for reviewers, draft Data safety answers and the store listing text. Store graphics are in `store/`.

## Project structure

```
App.tsx                 Entry point: stack navigator and session restore on launch
screens/                One file per screen (Welcome, Login, SelectStudent, Dashboard, ECCDChecklist, Profile, ...)
services/guardianApi.ts All backend calls, token storage, response validation, error handling
types/                  Environment typings (EXPO_PUBLIC_API_URL)
hooks/use-app-theme.tsx Display mode (light / dark / system) context and persistence
constants/theme.ts      Colors, including the DarkColors palette
components/, hooks/     Shared UI helpers
```

The app does **not** use expo-router. Navigation is a single React Navigation stack defined in `App.tsx`, and new screens must be registered there.

## How it works

- **Startup:** `restoreGuardianSession()` checks for a stored token and validates it against `/guardian/profile`. A valid token opens **SelectStudent**. Otherwise the app shows **Welcome**, or **Login** if there was an error.
- **API layer:** every request goes through `requestJson()` in `services/guardianApi.ts`. It applies a 15-second timeout, attaches the bearer token, and checks the response shape with runtime type guards. Failures are converted into a `GuardianApiError` with a user-facing message. Add new endpoints there rather than calling `fetch` from screens.
- **Tokens:** these are Sanctum tokens that expire after 12 hours. The password is never stored. With **Remember me**, the token is saved in the device's secure storage (iOS Keychain, readable only while the device is unlocked and never synced to other devices; Android Keystore). Otherwise it is kept in memory only. The checkbox appears only where secure storage is available, so it is hidden on web. If a stale or corrupted stored entry (e.g. after a reinstall) blocks saving or reading, it is deleted and the save is retried once. If the token still can't be saved, it is revoked on the server and the reason is shown. Any 401/403 response clears the stored token.
- **Child photos:** these are protected by the same auth as the API. `getGuardianChildPhotoSource()` checks that the photo URL points to the configured backend, fetches the image with the bearer token, accepts only JPEG/PNG/GIF up to 5 MB, and returns a `data:` URI. The token never reaches `<Image>` or its cache, and this works on web, where images can't send auth headers. The Dashboard and student list show a spinner while a photo loads and the person placeholder if there's no photo or it fails to load. A failed photo doesn't block the rest of the screen.
- **Responsive layout:** screens work from small phones (320 pt) up to tablets and desktop browsers. `Layout` in `constants/theme.ts` sets the limits:
  - `maxContentWidth` (640) and `maxFormWidth` (380) center and cap content on wide screens, so phones look the same as before.
  - `compactHeight` / `smallWidth` switch on tighter spacing for short or narrow phones.
  - Every screen's main content scrolls when it doesn't fit (short phones, large system font sizes).
  - Login stays above the keyboard.
  - On screens wider than the portrait background artwork (desktop web, landscape tablets), Welcome shows the whole illustration in a centered frame over a blurred copy, instead of zooming in and cropping it.
  - Sizes come from `useWindowDimensions()`, not a one-time `Dimensions.get()`, so they follow window resizes.
- **Development areas (Domain screen):** tapping a Recent Activity card on the Dashboard, or any of the 7 development areas on the ECCD Checklist screen, opens `screens/DomainScreen.tsx`. It shows:
  - an evaluation selector (1st/2nd/3rd Eval, with dates)
  - the raw score, scaled score, and how many skills were assessed
  - the interpretation of the scaled score (e.g. "Average development"), with a "Why this result?" link
  - every checklist item with ✓ (present), — (not present), or a clock icon (not yet assessed), plus the teacher's comment
  - **Things to try at home:** the items marked — in the latest evaluation of this area
  - a "No results yet" panel when the teacher hasn't recorded that evaluation
  - loading and error-with-retry states, and pull-to-refresh

  Data comes from `getGuardianChildDomain()` (`GET /guardian/children/{id}/domains/{domain}`). Domain names, icons and colors live in `constants/eccd-domains.ts` (official Child's Record 2 order).
- **Why this result? (Insights screen):** the Dashboard's **Latest result** card and the Domain screen's "Why this result?" link open `screens/InsightsScreen.tsx`. For each evaluation with a date, it shows:
  - the standard score and overall interpretation, or how many of the 7 areas are scored so far
  - notes, e.g. items not answered yet, or an age outside the 3y1m–5y11m range
  - the numbered scoring steps: age in 30-day months, age band, raw score, scaled score, sum, standard score
  - each area's ✓ count, scaled score and interpretation (tap an area to open it)
  - "Things to try at home" for every area
  - a note that results suggest development and are not a diagnosis

  Data comes from `getGuardianChildInsights()` (`GET /guardian/children/{id}/insights`). The text is produced by the backend expert system, so it always matches what the teacher sees. The screen is read-only.
- **Recent Activities:** the Dashboard shows the 3 domains the teacher updated most recently, newest first, using `recent_activity` from the dashboard API. Until the teacher records anything, it shows the first three official domains. The Dashboard reloads whenever it comes back into view and supports pull-to-refresh, so a new teacher entry moves that domain to the front.
- **Profile screens** (`screens/profile/`):
  - **Profile:** shows the signed-in guardian's name, email and relationship.
  - **Guardian Details** and **Child Details:** show every stored field (contact number, address; birthday, school, both parents, siblings, birth order).
  - **Contact Us:** lists the teacher handling each child, with Call / Text buttons.
  - **Notifications:** a feed of teacher updates across all children, newest first. It's built from each child's dashboard `recent_activity`, since there are no push notifications. Tapping an update opens that domain.
  - **Language:** saves English/Filipino on the device only (there is no language field on the server, and the app is English-only for now).
  - **Change Password** (`ChangePasswordScreen`):
    - The form takes the current password, a new password and its confirmation, each with a show/hide toggle.
    - It checks the server's rules before sending: at least 12 characters, at most 72 bytes, different from the current password, and the two new entries must match.
    - The server's own field errors appear under the matching input. The button shows "Saving..." while the request runs.
    - On success it shows a confirmation. The server keeps this session and signs out the guardian's other devices.
    - It calls `changeGuardianPassword()` (`PUT /guardian/password`).
- **Opening the app from the verification email:** the app's URL scheme is `littlestep` (`app.json`). After a guardian verifies their email, the backend's confirmation page opens `littlestep://login?verified=1`. In Expo Go it opens `exp://<host>:8081/--/login?verified=1`; set `GUARDIAN_APP_LOGIN_URL` on the backend to match.
  - **What handles it:** `linking` in `App.tsx` (native only) shows **Login** with "Your email is verified. Log in with your email and password to continue."
  - **What's accepted:** only the `login` path, and only the `verified=1` value. Other paths and query parameters are ignored, so a link can't open a protected screen or put its own text on screen.
  - **Already signed in:** the link is ignored (`hasGuardianSession()`).
  - **No automatic sign-in:** the guardian always signs in with their own password.
  - **Back button:** when the app was opened straight on Login, Back goes to Welcome.
  - **After changing the scheme:** rebuild development and store builds. Expo Go doesn't need a rebuild.
- **Default password reminder:** a teacher who leaves the password empty gives the guardian a shared default password.
  - **When it shows:** the login response's `must_change_password` is true, so **Select Student** shows a pop-up asking the guardian to change it (`components/password-reminder.tsx`).
  - **Its buttons:** **Change Password** opens the Change Password screen. **Remind me later** hides it until the next sign-in.
  - **Profile hint:** while the default password is still in use, Profile → Change Password says so.
  - **Where the state lives:** `guardianApi.ts` (`shouldShowPasswordReminder()`, `dismissPasswordReminder()`, `isUsingDefaultPassword()`).
  - **Storage:** with Remember me, the flag sits in secure storage next to the token, so the reminder returns after an app restart. Otherwise it is kept in memory. It is cleared on sign-out, on a 401/403, and once the password is changed.
  - **No hardcoded password:** the app never stores or compares the default password itself. The server decides.
- **Loading data on a screen:** use `useApiResource(loader)` (`hooks/use-api-resource.ts`).
  - It loads when the screen comes into view and provides `retry` and pull-to-refresh.
  - On a 401/403 it sends the guardian back to Login (`guardianApi` has already cleared the token). Screens that send data (like Change Password) get the same behavior from `useSessionEndedRedirect()`.
  - Pair it with `LoadingState` / `ErrorState` / `EmptyState` from `components/screen-state.tsx`, and with `ReadOnlyField` for detail rows.
  - `services/format.ts` has the shared date and name formatting.
- **Display mode:** Profile → Display lets the guardian choose **Light** (default), **Dark**, or **System** (follows the device setting and updates live). The choice is held in `hooks/use-app-theme.tsx` (`AppThemeProvider` wraps the app in `App.tsx`) and saved on the device (SecureStore on iOS/Android, `localStorage` on web). Screens call `useAppTheme()` and apply `DarkColors` from `constants/theme.ts` when `isDark` is true. Light mode keeps each screen's original colors. Dark mode applies to every screen and the navigation background. On the illustrated screens (Welcome, Login, Select Student, Dashboard, ECCD Checklist), the artwork is dimmed and the logo sits on a light badge so it stays readable. Category and activity cards keep their pastel colors in both modes. New screens should follow the same pattern: import `useAppTheme()` and add `isDark && darkStyles.*` overrides.

## Expo documentation

This project targets Expo SDK 58. Use the versioned docs at https://docs.expo.dev/versions/v58.0.0/, because APIs differ between SDK versions.
