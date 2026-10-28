<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Guardian accounts no longer share one default password: teachers hand out a temporary password,
 * and this flag tells the app to ask the guardian to change it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('password');
        });

        // Guardians still signing in with the old shared default password keep getting the reminder.
        DB::table('users')->where('role', 'guardian')->orderBy('id')->each(function (object $user) {
            if (Hash::check('Password123@', $user->password)) {
                DB::table('users')->where('id', $user->id)->update(['must_change_password' => true]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('must_change_password');
        });
    }
};
