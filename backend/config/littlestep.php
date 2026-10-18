<?php

return [
    /*
     * Password given to a guardian account when the teacher leaves the password
     * fields empty on Guardians → Add Guardian. Signing in to the mobile app with it
     * returns must_change_password, and the app asks the guardian to change it.
     */
    'guardian_default_password' => env('GUARDIAN_DEFAULT_PASSWORD', 'Password123@'),
];
