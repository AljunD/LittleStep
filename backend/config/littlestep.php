<?php

return [
    /*
     * Password given to a guardian account when the teacher leaves the password
     * fields empty on Guardians → Add Guardian. Guardians should change it with
     * "Forgot password" after their first sign-in.
     */
    'guardian_default_password' => env('GUARDIAN_DEFAULT_PASSWORD', 'Password123@'),
];
