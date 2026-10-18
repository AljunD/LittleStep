# LittleStep — Mobile (Guardian App)

Expo / React Native app (TypeScript) that guardians use to view their children's ECCD progress. It is a client of the LittleStep Laravel backend's guardian API (`/api/v1/guardian/*`). See [`../backend/README.md`](../backend/README.md).

**Stack:** Expo SDK 57, React Native 0.86, React 19, React Navigation (stack), `expo-secure-store`.

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
- **Display mode:** Profile → Display lets the guardian choose **Light** (default), **Dark**, or **System** (follows the device setting and updates live). The choice is held in `hooks/use-app-theme.tsx` (`AppThemeProvider` wraps the app in `App.tsx`) and saved on the device (SecureStore on iOS/Android, `localStorage` on web). Screens call `useAppTheme()` and apply `DarkColors` from `constants/theme.ts` when `isDark` is true. Light mode keeps each screen's original colors. Dark mode applies to every screen and the navigation background. On the illustrated screens (Welcome, Login, Select Student, Dashboard, ECCD Checklist), the artwork is dimmed and the logo sits on a light badge so it stays readable. Category and activity cards keep their pastel colors in both modes. New screens should follow the same pattern: import `useAppTheme()` and add `isDark && darkStyles.*` overrides.

## Expo documentation

This project targets Expo SDK 57. Use the versioned docs at https://docs.expo.dev/versions/v57.0.0/, because APIs differ between SDK versions.
