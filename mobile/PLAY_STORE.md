# Publishing LittleStep on Google Play

Checklist and draft answers for releasing the guardian app. Double-check every answer against the current Play Console
forms before submitting: Google changes them often.

## Before you start

- [ ] The backend is live at a permanent **https://** address (see `backend/README.md` → *Deployment*).
- [ ] `PRIVACY_CONTROLLER_NAME`, `PRIVACY_CONTACT_EMAIL` and `PRIVACY_CONTACT_PHONE` are set on the server, so
      `https://YOUR-DOMAIN/privacy` shows the day care center's details.
- [ ] A Google Play developer account (US$25 one-time, paid by credit/debit card; prepaid cards are not accepted).
      Ideally owned by the day care center or barangay, not a student's personal account.
- [ ] A free Expo account (expo.dev) and the EAS CLI: `npm install -g eas-cli`, then `eas login`.

## 1. Connect the app to the server (once)

Run from `mobile/`:

```bash
eas init
eas env:create --environment preview --name EXPO_PUBLIC_API_URL --value https://YOUR-DOMAIN --visibility plaintext
eas env:create --environment production --name EXPO_PUBLIC_API_URL --value https://YOUR-DOMAIN --visibility plaintext
```

`eas init` links the project to your Expo account and adds its `projectId` to `app.json`; commit that change.
The app identity is set in `app.json`: name **LittleStep**, Android package **`ph.littlestep.guardian`**.
**The package name cannot be changed after the first upload to Google Play**, so settle it before then.

## 2. Test builds (APK for phones)

```bash
eas build -p android --profile preview
```

EAS prints a link and QR code; open it on an Android phone to install. Use this to test with real parents before
publishing.

## 3. Store build (AAB for Google Play)

```bash
eas build -p android --profile production
```

Download the `.aab` and upload it in Play Console (or use `eas submit -p android`). EAS raises the version code
automatically on every production build.

## 4. Closed testing (new personal accounts)

Personal developer accounts created after 13 November 2023 must run a **closed test with at least 12 testers for
14 days in a row** before they can publish to everyone. Organization accounts are exempt. The day care center's
parents are ideal testers: add their Google account emails as testers, share the opt-in link, and ask them to use the
app during the 14 days.

## 5. App access (for Google's reviewers)

The app needs a sign-in, so give reviewers a **demo guardian account** linked to a **sample child** (never a real
child): create it in the teacher portal, verify its email, and enter the email and password under
*App content → App access*.

## 6. Data safety form (draft answers)

The app only sends the guardian's **email address and password** (to sign in). Everything else it shows is
downloaded from the day care center's server; the app has no ads, analytics or tracking.

| Question | Answer |
|---|---|
| Does the app collect or share any required user data types? | **Yes** |
| Is all user data encrypted in transit? | **Yes** (HTTPS only; the app refuses plain http outside development) |
| Can users request that their data be deleted? | **Yes**, through the day care center (privacy policy, section 8) |
| Data types collected | **Personal info → Email address** |
| Shared with third parties? | **No** |
| Processed ephemerally? | No |
| Required or optional? | Required |
| Purpose | **Account management**, **App functionality** |

Also enter the privacy policy URL: `https://YOUR-DOMAIN/privacy`.

## 7. Other App content answers

- **Target audience:** adults (18+). The app is for parents and guardians, not for children to use.
- **Ads:** No ads.
- **Content rating:** complete the questionnaire (no violence, no user-generated public content, no purchases).
- **Government app:** answer according to who owns the developer account (for example a barangay).

## 8. Store listing (draft)

- **App name:** LittleStep
- **Short description (max 80):** Follow your child's ECCD development progress from your day care center.
- **Full description:**

  > LittleStep lets parents and guardians follow their child's growth at the day care center.
  >
  > • See your child's Early Childhood Care and Development (ECCD) Checklist results for each evaluation
  > • Understand each development area, with what your child can practise at home
  > • Read your teacher's notes and updates
  > • Your child's information is visible only to you and your child's teacher
  >
  > Your account is created by your child's teacher. Ask the day care center for your sign-in details.

- **Graphics** (in `mobile/store/`): `play-icon-512.png` (512×512 icon) and `play-feature-graphic-1024x500.png`.
  Add at least 2 phone screenshots taken from the test build. The icons are made from `littlestep-mark.png`
  (256×256); replace them with a higher-resolution logo if you have one.
