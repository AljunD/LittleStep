<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\ChildrenController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\LogsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Default welcome route
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('auth.register.form');
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register.submit');

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('auth.login.submit');

    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('auth.password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('auth.password.email');
    
    // Updated name from 'auth.password.reset' to 'password.reset'
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    
    Route::post('/reset-password', [AuthController::class, 'reset'])->name('auth.password.update');
});

/*
|--------------------------------------------------------------------------
| Email Verification Routes
|--------------------------------------------------------------------------
*/
Route::get('/email/verify', function () {
    return view('emails.verify-notice');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('success', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/email/confirmation', function () {
    return view('emails.verified');
})->middleware('auth')->name('verification.confirmation');

/*
|--------------------------------------------------------------------------
| Dashboard (Teacher-only)
|--------------------------------------------------------------------------
*/
// Configured to load runtime dashboard variables through DashboardController
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'teacher'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Guardians Routes (Teacher-only)
|--------------------------------------------------------------------------
*/
Route::prefix('guardians')->middleware(['auth', 'verified', 'teacher'])->group(function () {
    
    // 1. Core Static Lists & Creation Actions
    Route::get('/', [GuardianController::class, 'index'])->name('guardians.index');
    Route::get('/create', [GuardianController::class, 'create'])->name('guardians.create');
    Route::post('/', [GuardianController::class, 'store'])->name('guardians.store');

    // 2. Child Management Engines
    Route::get('/{id}/create-child', [GuardianController::class, 'createChild'])->name('guardians.create-child');
    Route::post('/{id}/store-child', [GuardianController::class, 'storeChild'])->name('guardians.store-child');
    Route::delete('/{guardianId}/child/{childId}', [GuardianController::class, 'unlinkChild'])->name('guardians.unlink-child');

    // 3. Dynamic Unlink Preview Interface
    Route::get('/{id}/archive-child', [GuardianController::class, 'archiveChild'])->name('guardians.archive-child');

    // 4. Primary Wildcard ID Record Operations (Keep at the bottom)
    Route::get('/{id}', [GuardianController::class, 'show'])->name('guardians.show');
    Route::get('/{id}/edit', [GuardianController::class, 'edit'])->name('guardians.edit');
    Route::put('/{id}', [GuardianController::class, 'update'])->name('guardians.update');
    Route::delete('/{id}', [GuardianController::class, 'destroy'])->name('guardians.destroy');
});

/*
|--------------------------------------------------------------------------
| Children Routes (Teacher-only, full CRUD)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'teacher'])->group(function () {
    // ✅ Use "children" consistently so Blade calls like route('children.index') work
    Route::resource('children', ChildrenController::class);
});

/*
|--------------------------------------------------------------------------
| Archives (Teacher-only, soft delete + restore)
|--------------------------------------------------------------------------
*/
Route::prefix('archive')->middleware(['auth', 'verified', 'teacher'])->group(function () {
    Route::get('/', [ArchiveController::class, 'index'])->name('archives.index');
    Route::patch('/{id}/restore', [ArchiveController::class, 'restore'])->name('guardians.restore');
    
    // Add child restore route
    Route::patch('/child/{id}/restore', [ArchiveController::class, 'restoreChild'])->name('children.restore');
});

/*
|--------------------------------------------------------------------------
| Progress Routes (Static Preview)
|--------------------------------------------------------------------------
|
| These routes are currently static previews that return Blade views.
| Later, you can replace the closures with controller methods.
|
*/
Route::prefix('progress')->middleware(['auth', 'verified', 'teacher'])->group(function () {
    Route::get('/', [ProgressController::class, 'index'])->name('progress.index');   

    // Select domain for evaluation
    Route::get('/select-domain/{child_id?}', fn() => view('progress.select-domain'))
        ->name('progress.select-domain');

    // Create new evaluation
    Route::get('/create/{child_id?}', fn() => view('progress.create'))
        ->name('progress.create');

    // Create new observation
    Route::get('/add-observation/{child_id?}', fn() => view('progress.create-observation'))
        ->name('progress.add-observation');

    // Show progress record
    Route::get('/show/{id?}', fn() => view('progress.show'))
        ->name('progress.show');

    // Edit progress record
    Route::get('/edit/{id?}', fn() => view('progress.edit'))
        ->name('progress.edit');
});


/*
|--------------------------------------------------------------------------
| System Logs (Teacher-only)
|--------------------------------------------------------------------------
*/
Route::get('/logs', [LogsController::class, 'index'])
    ->middleware(['auth','verified','teacher'])
    ->name('logs.index');