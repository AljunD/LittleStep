<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Models\User;
use App\Models\Teacher;

class AuthController extends Controller
{
    /**
     * Show registration form
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration securely
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name'        => 'required|string|max:255',
            'middle_name'       => 'nullable|string|max:255',
            'last_name'         => 'required|string|max:255',
            'contact_number'    => 'required|string|max:20',
            'address'           => 'required|string|max:500',
            'email'             => 'required|string|email|max:255|unique:users',
            'password'          => 'required|string|min:8|confirmed',
        ]);

        // Create user account (teacher role only for web)
        $user = User::create([
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'teacher',
        ]);

        // Create teacher profile linked to user
        $teacher = Teacher::create([
            'user_id'        => $user->id,
            'first_name'     => $request->first_name,
            'middle_name'    => $request->middle_name,
            'last_name'      => $request->last_name,
            'contact_number' => $request->contact_number,
            'address'        => $request->address,
        ]);

        event(new Registered($user));
        Auth::login($user);

        // Record log
        recordLog('registered', 'Teacher', $teacher->id, 'Teacher account registered: ' . $teacher->first_name . ' ' . $teacher->last_name);

        return redirect()->route('verification.notice')
            ->with('success', 'Registration successful! Please verify your email.');
    }

    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login securely
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Only teachers can use the web
            if ($user->role !== 'teacher') {
                Auth::logout();
                recordLog('login_denied', 'User', $user->id, 'Non-teacher attempted login: ' . $user->email);
                return back()->withErrors([
                    'email' => 'Only teachers are allowed to access the web portal.',
                ]);
            }

            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                recordLog('login_denied', 'User', $user->id, 'Unverified email attempted login: ' . $user->email);
                return redirect()->route('verification.notice')
                    ->withErrors(['email' => 'You must verify your email before logging in.']);
            }

            // Record successful login
            recordLog('login', 'User', $user->id, 'Teacher logged in: ' . $user->email);

            return redirect()->intended('/dashboard');
        }

        // Record failed login attempt
        recordLog('login_failed', 'User', 0, 'Failed login attempt for email: ' . $request->email);

        return back()->withErrors([
            'email' => 'Invalid credentials provided.',
        ]);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        $userId = $user ? $user->id : null;

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($userId) {
            recordLog('logout', 'User', $userId, 'Teacher logged out: ' . $user->email); //[cite: 34]
        }

        return redirect()->route('login'); // Updated from 'auth.login.form'
    }

    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send reset link email securely
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            recordLog('password_reset_link', 'User', 0, 'Password reset link sent to: ' . $request->email);
            return back()->with(['success' => __($status)]);
        }

        return back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show reset password form
     */
    public function showResetForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Handle password reset securely
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                recordLog('password_reset', 'User', $user->id, 'Password reset for: ' . $user->email);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('auth.login.form')->with('status', 'Password reset successfully! You may now log in.')
            : back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * Handle email verification securely
     */
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        $user = Auth::user();

        Mail::send('emails.account-details', [
            'user' => $user,
        ], function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Your KidWatch2 Account Details');
        });

        recordLog('email_verified', 'User', $user->id, 'Email verified for: ' . $user->email);

        return redirect()->route('verification.confirmation');
    }
}
