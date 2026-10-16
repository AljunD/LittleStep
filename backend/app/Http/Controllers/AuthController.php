<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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
            'sex'               => 'required|string|in:Male,Female', // Added validation
            'contact_number'    => 'required|string|max:20',
            'address'           => 'required|string|max:500',
            'email'             => 'required|string|email|max:255|unique:users',
            'password'          => 'required|string|min:8|confirmed',
        ]);

        $cleanEmail = Str::lower(trim($request->email));

        $user = User::create([
            'email'    => $cleanEmail,
            'password' => Hash::make($request->password),
            'role'     => 'teacher',
        ]);

        $teacher = Teacher::create([
            'user_id'        => $user->id,
            'first_name'     => $request->first_name,
            'middle_name'    => $request->middle_name,
            'last_name'      => $request->last_name,
            'sex'            => $request->sex, // Added field
            'contact_number' => $request->contact_number,
            'address'        => $request->address,
        ]);

        event(new Registered($user));
        Auth::login($user);

        recordLog('registered', 'Teacher', $teacher->id, 'Teacher account registered: ' . $teacher->first_name . ' ' . $teacher->last_name);

        return redirect()->route('verification.notice')
            ->with('success', 'Registration successful! Please verify your email.');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials['email'] = Str::lower(trim($credentials['email']));

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->role !== 'teacher') {
                Auth::logout();
                recordLog('login_denied', 'User', $user->id, 'Non-teacher attempted login: ' . $user->email);
                return back()->withErrors([
                    'email' => 'Only teachers are allowed to access the web portal.',
                ])->onlyInput('email');
            }

            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                recordLog('login_denied', 'User', $user->id, 'Unverified email attempted login: ' . $user->email);
                return redirect()->route('verification.notice')
                    ->withErrors(['email' => 'You must verify your email before logging in.']);
            }

            recordLog('login', 'User', $user->id, 'Teacher logged in: ' . $user->email);

            return redirect()->intended('/dashboard');
        }

        recordLog('login_failed', 'User', 0, 'Failed login attempt for email: ' . $request->email);

        return back()->withErrors([
            'email' => 'Invalid credentials provided.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $userId = $user ? $user->id : null;

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($userId) {
            recordLog('logout', 'User', $userId, 'Teacher logged out: ' . $user->email);
        }

        return redirect()->route('login');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = Str::lower(trim($request->email));

        $status = Password::sendResetLink(['email' => $email]);

        if ($status === Password::RESET_LINK_SENT) {
            recordLog('password_reset_link', 'User', 0, 'Password reset link sent to: ' . $email);
            return back()->with(['success' => __($status)]);
        }

        return back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    public function showResetForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            [
                'token' => $request->token,
                'email' => Str::lower(trim($request->email)),
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                recordLog('password_reset', 'User', $user->id, 'Password reset for: ' . $user->email);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Password reset successfully! You may now log in.')
            : back()->withErrors(['email' => [__($status)]])->onlyInput('email');
    }

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