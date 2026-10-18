<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChildrenController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\ProgressController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::prefix('auth')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('auth.register.form');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('auth.register.submit');

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('auth.login.submit');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth')
        ->name('auth.logout');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('auth.password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
        ->middleware('throttle:5,1')
        ->name('auth.password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');

    Route::post('/reset-password', [AuthController::class, 'reset'])
        ->middleware('throttle:5,1')
        ->name('auth.password.update');
});

Route::get('/email/verify', function () {
    return view('emails.verify-notice');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('success', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/email/confirmation', function () {
    return view('emails.verified');
})->name('verification.confirmation');

Route::get('/guardian/email/confirmation', function () {
    return view('emails.guardian-verified');
})->name('guardian.verification.confirmation');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'teacher'])
    ->name('dashboard');

Route::prefix('guardians')->middleware(['auth', 'verified', 'teacher'])->group(function () {

    Route::get('/', [GuardianController::class, 'index'])->name('guardians.index');
    Route::get('/create', [GuardianController::class, 'create'])->name('guardians.create');
    Route::post('/', [GuardianController::class, 'store'])->name('guardians.store');

    Route::get('/{id}/create-child', [GuardianController::class, 'createChild'])->name('guardians.create-child');
    Route::post('/{id}/store-child', [GuardianController::class, 'storeChild'])->name('guardians.store-child');
    Route::delete('/{guardianId}/child/{childId}', [GuardianController::class, 'unlinkChild'])->name('guardians.unlink-child');

    Route::get('/{id}/archive-child', [GuardianController::class, 'archiveChild'])->name('guardians.archive-child');

    Route::get('/{id}', [GuardianController::class, 'show'])->name('guardians.show');
    Route::get('/{id}/edit', [GuardianController::class, 'edit'])->name('guardians.edit');
    Route::put('/{id}', [GuardianController::class, 'update'])->name('guardians.update');
    Route::delete('/{id}', [GuardianController::class, 'destroy'])->name('guardians.destroy');
});

Route::middleware(['auth', 'verified', 'teacher'])->group(function () {

    // Children are registered through a guardian (guardians.create-child); there is no standalone create form.
    Route::resource('children', ChildrenController::class)->except(['create']);
    Route::get('/children/{id}/photo', [ChildrenController::class, 'photo'])->name('children.photo');
});

Route::prefix('archive')->middleware(['auth', 'verified', 'teacher'])->group(function () {
    Route::get('/', [ArchiveController::class, 'index'])->name('archives.index');
    Route::patch('/{id}/restore', [ArchiveController::class, 'restore'])->name('guardians.restore');

    Route::patch('/child/{id}/restore', [ArchiveController::class, 'restoreChild'])->name('children.restore');
});

Route::prefix('progress')->middleware(['auth', 'verified', 'teacher'])->group(function () {
    Route::get('/', [ProgressController::class, 'index'])
        ->name('progress.index');

    Route::get('/select-domain/{child_id?}', [ProgressController::class, 'selectDomain'])
        ->name('progress.select-domain');

    Route::get('/create/{child_id?}', [ProgressController::class, 'create'])
        ->name('progress.create');

    Route::get('/add-observation/{child_id?}', [ProgressController::class, 'addObservation'])
        ->name('progress.add-observation');

    Route::post('/store-observation', [ProgressController::class, 'storeObservation'])
        ->name('progress.observation.store');

    Route::get('/show/{id?}', [ProgressController::class, 'show'])
        ->name('progress.show');

    Route::get('/{progressRecord}/download-pdf', [ProgressController::class, 'downloadPdf'])
        ->name('progress.download-pdf');

    Route::get('/{progressRecord}/pdf', [ProgressController::class, 'downloadPdf'])
        ->name('progress.pdf');

    Route::get('/edit/{id?}', [ProgressController::class, 'edit'])
        ->name('progress.edit');

    Route::put('/edit/{id}', [ProgressController::class, 'update'])
        ->name('progress.update');

    Route::post('/store', [ProgressController::class, 'store'])
        ->name('progress.store');
});

Route::get('/logs', [LogsController::class, 'index'])
    ->middleware(['auth', 'verified', 'teacher'])
    ->name('logs.index');
