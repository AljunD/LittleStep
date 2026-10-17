<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        ], 200);
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
