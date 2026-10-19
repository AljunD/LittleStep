<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail context: where each logged action came from, plus indexes for the
 * per-user System Logs page (always filtered by user, newest first).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('details');
            $table->string('user_agent', 255)->nullable()->after('ip_address');
            $table->index(['user_id', 'created_at'], 'logs_user_created_index');
            $table->index(['user_id', 'action'], 'logs_user_action_index');
        });
    }

    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropIndex('logs_user_created_index');
            $table->dropIndex('logs_user_action_index');
            $table->dropColumn(['ip_address', 'user_agent']);
        });
    }
};
