<?php

use App\Models\Log;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Applies the optional AUDIT_LOG_RETENTION_DAYS policy (no-op when it is not set).
Schedule::command('model:prune', ['--model' => [Log::class]])->daily();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
