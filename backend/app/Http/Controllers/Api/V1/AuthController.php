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

        $token = $user->createToken('guardian-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'guardian' => $user->guardian, // guardian profile from guardians table
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
