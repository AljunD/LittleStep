<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\FormRules;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    // POST /guardian/login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|max:1024',
        ]);

        $user = User::where('email', Str::lower(trim($credentials['email'])))->first();
        if (! $user || ! Hash::check($credentials['password'], $user->password) || $user->role !== 'guardian' || ! $user->guardian) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'error' => 'Email not verified. Please verify your email before logging in.',
            ], 403);
        }

        $expiresAt = now()->addHours(12);
        $token = $user->createToken('guardian-mobile', ['*'], $expiresAt)->plainTextToken;
        $guardian = $user->guardian;

        return response()->json([
            'token' => $token,
            'guardian' => [
                'id' => $guardian->id,
                'first_name' => $guardian->first_name,
                'middle_name' => $guardian->middle_name,
                'last_name' => $guardian->last_name,
            ],
            'expires_at' => $expiresAt->toIso8601String(),
            // The password just verified is the default one teachers give new accounts, so the app asks for a new one.
            'must_change_password' => self::isDefaultPassword($credentials['password']),
        ], 200);
    }

    // PUT /guardian/password
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string', 'max:1024', 'current_password:sanctum'],
            'password' => [...FormRules::password(), 'different:current_password', static function (string $attribute, mixed $value, Closure $fail): void {
                if (is_string($value) && self::isDefaultPassword($value)) {
                    $fail('Choose a password other than the default password.');
                }
            }],
        ], [
            'current_password.current_password' => 'Your current password is incorrect.',
            'password.different' => 'The new password must be different from your current password.',
        ]);

        $user = $request->user();
        $user->forceFill(['password' => Hash::make($validated['password'])])->save();

        // Sign out every other device; this app keeps its current session.
        $currentToken = $user->currentAccessToken();
        $user->tokens()
            ->when($currentToken instanceof PersonalAccessToken, fn ($tokens) => $tokens->whereKeyNot($currentToken->getKey()))
            ->delete();

        recordLog('password_changed', 'Guardian', $user->guardian->id, 'Guardian changed their password in the mobile app.');

        return response()->json(['message' => 'Your password has been changed.'], 200);
    }

    private static function isDefaultPassword(string $password): bool
    {
        $default = (string) config('littlestep.guardian_default_password');

        return $default !== '' && hash_equals($default, $password);
    }

    // POST /guardian/logout
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();

            return response()->json(['message' => 'Logged out'], 200);
        }

        return response()->json(['error' => 'No active session'], 401);
    }

    // OPTIONAL: Logout everywhere (delete all tokens)
    public function logoutAll(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->tokens()->delete();

            return response()->json(['message' => 'Logged out from all devices'], 200);
        }

        return response()->json(['error' => 'No active session'], 401);
    }
}
