<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use App\Support\FormRules;
use App\Support\SafeMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);
        $validated = $request->validate([
            ...FormRules::teacherProfile(),
            'email' => 'required|string|email|max:255|unique:users',
            'password' => FormRules::password(),
        ], FormRules::messages());

        $cleanEmail = Str::lower(trim($request->email));

        [$user, $teacher] = DB::transaction(function () use ($request, $validated, $cleanEmail) {
            $user = User::create([
                'email' => $cleanEmail,
                'password' => Hash::make($request->password),
                'role' => 'teacher',
            ]);

            $teacher = Teacher::create(['user_id' => $user->id] + FormRules::teacherAttributes($validated));

            return [$user, $teacher];
        });

        $verificationSent = SafeMail::attempt(fn () => event(new Registered($user)));
        Auth::login($user);
        $request->session()->regenerate();

        recordLog('registered', 'Teacher', $teacher->id, 'Teacher account registered.', $user->id);

        return redirect()->route('verification.notice')
            ->with('success', 'Registration successful! Please verify your email.')
            ->with('error', $verificationSent ? null : 'The verification email could not be sent because the mail server could not be reached. Try "Resend" once the connection is back.');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|max:1024',
        ]);

        $credentials['email'] = Str::lower(trim($credentials['email']));

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->role !== 'teacher') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                recordLog('login_denied', 'User', $user->id, 'Non-teacher attempted web login.', $user->id);

                return back()->withErrors([
                    'email' => 'Only teachers are allowed to access the web portal.',
                ])->onlyInput('email');
            }

            if (! $user->hasVerifiedEmail()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                recordLog('login_denied', 'User', $user->id, 'Unverified email attempted web login.', $user->id);

                return back()->withErrors([
                    'email' => 'You must verify your email before logging in. Check your inbox for the verification link.',
                ])->onlyInput('email');
            }

            recordLog('login', 'User', $user->id, 'Teacher logged in.', $user->id);

            return redirect()->intended('/dashboard');
        }

        // Attribute the attempt to the targeted account (if any) so its owner sees it in System Logs.
        $targetedUserId = User::where('email', $credentials['email'])->value('id');
        recordLog('login_failed', 'User', $targetedUserId, 'Failed sign-in attempt on the web portal.', $targetedUserId);

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
            recordLog('logout', 'User', $userId, 'Teacher logged out.', $userId);
        }

        return redirect()->route('login');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|max:255']);

        $email = Str::lower(trim($request->email));

        SafeMail::attempt(fn () => Password::sendResetLink(['email' => $email]));

        return back()->with(['success' => 'If an account exists for that email, a password reset link will be sent.']);
    }

    public function showResetForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => FormRules::password(),
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
                $user->tokens()->delete();

                recordLog('password_reset', 'User', $user->id, 'Password reset.', $user->id);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Password reset successfully! You may now log in.')
            : back()->withErrors(['email' => [__($status)]])->onlyInput('email');
    }

    public function verify(Request $request)
    {
        $userId = $request->route('id');
        $hash = $request->route('hash');

        $user = User::findOrFail($userId);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();

            SafeMail::attempt(fn () => Mail::send('emails.account-details', [
                'user' => $user,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Your LittleStep Account Is Verified');
            }));

            recordLog('email_verified', 'User', $user->id, 'User email address verified.', $user->id);
        }

        if ($user->role === 'teacher') {
            return redirect()->route('login')->with('status', 'Your email has been verified successfully. You may now log in.');
        }

        return redirect()->route('guardian.verification.confirmation')->with('status', 'Your email has been verified successfully.');
    }
}
