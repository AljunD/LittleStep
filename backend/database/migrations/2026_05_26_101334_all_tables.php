<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ====================== SESSIONS ======================
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity')->index();
            $table->timestamps();
        });

        // ====================== USERS ======================
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['teacher', 'guardian']);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        // ====================== TEACHERS ======================
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('sex', ['Male', 'Female']);
            $table->string('contact_number')->nullable();
            $table->text('address');
            $table->timestamps();
            $table->softDeletes();
        });

        // ====================== GUARDIANS ======================
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('sex', ['Male', 'Female']);
            $table->string('contact_number');
            $table->text('address');
            $table->string('relationship_to_child');
            $table->timestamps();
            $table->softDeletes();
        });

        // ====================== CHILDREN ======================
        Schema::create('children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_id')->constrained('guardians')->onDelete('cascade');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('sex', ['Male', 'Female']);
            $table->date('date_of_birth');
            $table->string('address')->nullable();
            $table->enum('handedness', ['right', 'left', 'both', 'not_yet_established']);
            $table->boolean('is_studying')->default(false);
            $table->string('school_name')->nullable();
            $table->string('fathers_name')->nullable();
            $table->integer('fathers_age')->nullable();
            $table->string('fathers_occupation')->nullable();
            $table->string('fathers_education')->nullable();
            $table->string('mothers_name')->nullable();
            $table->integer('mothers_age')->nullable();
            $table->string('mothers_occupation')->nullable();
            $table->string('mothers_education')->nullable();
            $table->integer('number_of_siblings')->nullable();
            $table->integer('birth_order')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ====================== PROGRESS RECORDS ======================
        Schema::create('progress_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->date('evaluation_date');
            $table->tinyInteger('evaluation_number');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        // ====================== DOMAINS ======================
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_record_id')->nullable()->constrained('progress_records')->onDelete('cascade');
            $table->enum('domain', [
                'gross_motor', 'fine_motor', 'self_help',
                'receptive_language', 'expressive_language',
                'cognitive', 'social_emotional'
            ]);
            $table->string('activity');
            $table->text('materials_and_procedure')->nullable();
            $table->string('item_number')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        // ====================== DOMAIN RESULTS ======================
        Schema::create('domain_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('domains')->onDelete('cascade');
            $table->enum('present', ['check', 'hyphen'])->default('hyphen');
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ====================== DOMAIN OBSERVATIONS ======================
        Schema::create('domain_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_record_id')->unique()->constrained('progress_records')->onDelete('cascade');
            $table->text('place_administered')->nullable();
            $table->text('background_observations')->nullable();
            $table->text('family_environment_notes')->nullable();
            $table->text('stimulating_activities_notes')->nullable();
            $table->text('home_environment_notes')->nullable();
            $table->text('other_notes')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        // ====================== DOMAIN SCORES ======================
        Schema::create('domain_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_record_id')->constrained('progress_records')->onDelete('cascade');
            $table->foreignId('domain_id')->constrained('domains')->onDelete('cascade');
            $table->smallInteger('raw_score')->nullable();
            $table->smallInteger('scaled_score')->nullable();
            $table->smallInteger('sum_scaled_score')->nullable();
            $table->smallInteger('standard_score')->nullable();
            $table->decimal('child_age', 4, 2)->nullable();
            $table->enum('interpretation_code', [
                'monitor_3m', 'monitor_6m', 'average',
                'slightly_advanced', 'highly_advanced'
            ])->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ====================== LOGS ======================
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ====================== PERSONAL ACCESS TOKENS ======================
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // ====================== PASSWORD RESET TOKENS ======================
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamps();
            $table->softDeletes();
        });

        // ====================== CACHE ======================
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->bigInteger('expiration')->index();
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->bigInteger('expiration')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('logs');
        Schema::dropIfExists('domain_scores');
        Schema::dropIfExists('domain_observations');
        Schema::dropIfExists('domain_results');
        Schema::dropIfExists('domains');
        Schema::dropIfExists('progress_records');
        Schema::dropIfExists('children');
        Schema::dropIfExists('guardians');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};