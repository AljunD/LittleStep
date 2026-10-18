<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GuardianController;
use Illuminate\Support\Facades\Route;

Route::prefix('/v1')->group(function () {
    Route::post('/guardian/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware(['auth:sanctum', 'guardian', 'verified'])->group(function () {
        Route::post('/guardian/logout', [AuthController::class, 'logout']);
        Route::get('/guardian/profile', [GuardianController::class, 'profile']);
        Route::get('/guardian/contacts', [GuardianController::class, 'contacts']);
        Route::get('/guardian/views', [GuardianController::class, 'views']);
        Route::get('/guardian/children', [GuardianController::class, 'children']);
        Route::get('/guardian/children/{id}', [GuardianController::class, 'showChild']);
        Route::get('/guardian/children/{id}/dashboard', [GuardianController::class, 'dashboard'])
            ->whereNumber('id');
        Route::get('/guardian/children/{id}/photo', [GuardianController::class, 'photo'])
            ->whereNumber('id');
        Route::get('/guardian/children/{id}/domains/{domain}', [GuardianController::class, 'domain'])
            ->whereNumber('id')
            ->whereIn('domain', array_keys(GuardianController::DOMAIN_LABELS));
    });

});