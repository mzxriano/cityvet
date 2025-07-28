<?php

use App\Http\Controllers\Web\ActivityController;
use App\Http\Controllers\Web\AnimalController;
use App\Http\Controllers\Web\BarangayController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Middleware\EnsureAdminSession;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AdminAuthController;
use App\Http\Controllers\Web\VaccineController;


Route::get('/successful-verification', function () {
    return view('mail.verification_successful');
})->name('email.successful');

Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('showLogin');
Route::post('/login', [AdminAuthController::class, 'login'])->name('login');
Route::get('/register', [AdminAuthController::class, 'showRegisterForm'])->name('showRegister');
Route::post('/register', [AdminAuthController::class, 'register'])->name('register');

// Admin Email Verification
Route::prefix('admin')->group(function () {
    Route::get('/email/verify', [App\Http\Controllers\Web\AdminAuthController::class, 'showVerificationNotice'])->name('admin.verification.notice');
    Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\Web\AdminAuthController::class, 'verifyEmail'])->name('admin.verification.verify');
    Route::post('/email/resend', [App\Http\Controllers\Web\AdminAuthController::class, 'resendVerificationEmail'])->name('admin.verification.resend');
});

Route::prefix('admin')->group(function () {

    // Admin Forgot Password
    Route::get('/forgot-password', [App\Http\Controllers\Web\AdminAuthController::class, 'showForgotPasswordForm'])->name('admin.forgot_password');
    Route::post('/forgot-password', [App\Http\Controllers\Web\AdminAuthController::class, 'sendResetLink'])->name('admin.forgot_password.send');
    Route::get('/reset-password/{token}', [App\Http\Controllers\Web\AdminAuthController::class, 'showResetPasswordForm'])->name('admin.reset_password');
    Route::post('/reset-password', [App\Http\Controllers\Web\AdminAuthController::class, 'resetPassword'])->name('admin.reset_password.submit');

    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    Route::middleware(['auth:admin', EnsureAdminSession::class, \App\Http\Middleware\EnsureAdminEmailIsVerified::class])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::prefix('activities')->group(function () {
            Route::get('/', [ActivityController::class, 'index'])->name('admin.activities');
            Route::get('/{id}/show', [ActivityController::class, 'show'])->name('admin.activities.show');
            Route::post('/', [ActivityController::class, 'create'])->name('admin.activities.store');
        });

        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('admin.users');
            Route::post('/', [UserController::class, 'store'])->name('admin.users.store');
            Route::get('/{id}/show', [UserController::class, 'show'])->name('admin.users.show');
            Route::put('/{id}', [UserController::class, 'edit'])->name('users.edit');
            Route::get('/search', [AnimalController::class,'searchOwner'])->name('search.owner');
        });

        Route::prefix('animals')->group(function () {
            Route::get('/', [AnimalController::class, 'index'])->name('admin.animals');
            Route::get('/{id}/show', [AnimalController::class, 'show'])->name('admin.animals.show');
            Route::post('/', [AnimalController::class, 'store'])->name('admin.animals.store');
            Route::put('/{id}', [AnimalController::class, 'update'])->name('admin.animals.update');
        });

        Route::get('/barangay', [BarangayController::class, 'index'])->name('admin.barangay');

        Route::prefix('vaccines')->group(function () {
            Route::get('/', [VaccineController::class, 'index'])->name('admin.vaccines');
            Route::get('/add', [VaccineController::class, 'create'])->name('admin.vaccines.add');
            Route::get('/{id}/show', [VaccineController::class, 'show'])->name('admin.vaccines.show');
            Route::post('/', [VaccineController::class, 'store'])->name('admin.vaccines.store');
            Route::get('/{id}/edit', [VaccineController::class, 'edit'])->name('admin.vaccines.edit');
            Route::put('/{id}', [VaccineController::class, 'update'])->name('admin.vaccines.update');
            Route::delete('/{id}', [VaccineController::class, 'destroy'])->name('admin.vaccines.destroy');
        });

        Route::get('/community', function () {
            return view('admin.community');
        })->name('admin.community');

        // Admin AJAX endpoints for community moderation
        Route::get('/community/pending-posts', [\App\Http\Controllers\Web\CommunityController::class, 'pending'])->name('admin.community.pending');
        Route::patch('/community/{id}/review', [\App\Http\Controllers\Web\CommunityController::class, 'review'])->name('admin.community.review');
        Route::get('/community/approved-posts', [\App\Http\Controllers\Web\CommunityController::class, 'approved'])->name('admin.community.approved');

        Route::get('/reports', function () {
            return view('admin.reports');
        })->name('admin.reports');

        Route::get('/archives', function () {
            return view('admin.archives');
        })->name('admin.archives');
        
    });
});

