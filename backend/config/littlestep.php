<?php

return [
    /*
     * Password given to a guardian account when the teacher leaves the password
     * fields empty on Guardians → Add Guardian. Signing in to the mobile app with it
     * returns must_change_password, and the app asks the guardian to change it.
     */
    'guardian_default_password' => env('GUARDIAN_DEFAULT_PASSWORD', 'Password123@'),

    /*
     * Deep link to the guardian app's Login screen, opened after a guardian verifies their email.
     * Installed app builds use the app scheme (littlestep://login). With Expo Go use
     * exp://<your-LAN-IP>:8081/--/login. Only littlestep, exp, exps and https links are accepted
     * (App\Support\GuardianAppLink); leave it empty to show the confirmation page without the button.
     */
    'guardian_app_login_url' => env('GUARDIAN_APP_LOGIN_URL', 'littlestep://login'),
];
