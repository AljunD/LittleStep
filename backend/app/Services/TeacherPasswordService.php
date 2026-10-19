<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherPasswordService
{
    /**
     * Save the new password and sign the account out everywhere except the current session:
     * a new remember token voids "remember me" cookies, API tokens are revoked, and other
     * stored sessions are deleted (only possible with the database session driver).
     */
    public function change(User $user, string $newPassword, string $currentSessionId): void
    {
        $user->forceFill([
            'password' => Hash::make($newPassword),
            'remember_token' => Str::random(60),
        ])->save();

        $user->tokens()->delete();

        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->where('user_id', $user->getKey())
                ->where('id', '!=', $currentSessionId)
                ->delete();
        }
    }
}
