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

Route::prefix('admin')->group(function () {

    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    Route::middleware(['auth:admin', EnsureAdminSession::class])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::prefix('activities')->group(function () {
            Route::get('/', [ActivityController::class, 'index'])->name('admin.activities');
            Route::post('/', [ActivityController::class, 'create'])->name('admin.activities.store');
        });

        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('admin.users');
            Route::post('/', [UserController::class, 'store'])->name('admin.users.store');
            Route::put('/{id}', [UserController::class, 'edit'])->name('users.edit');
            Route::get('/search', [AnimalController::class,'searchOwner'])->name('search.owner');
        });

        Route::prefix('animals')->group(function () {
            Route::get('/', [AnimalController::class, 'index'])->name('admin.animals');
            Route::post('/', [AnimalController::class, 'store'])->name('admin.animals.store');
            Route::put('/{id}', [AnimalController::class, 'update'])->name('admin.animals.update');
        });

        Route::get('/barangay', [BarangayController::class, 'index'])->name('admin.barangay');

        Route::prefix('vaccines')->group(function () {
            Route::get('/', [VaccineController::class, 'index'])->name('admin.vaccines');
            Route::get('/add', [VaccineController::class, 'create'])->name('admin.vaccines.add');
            Route::post('/', [VaccineController::class, 'store'])->name('admin.vaccines.store');
            Route::get('/{id}/edit', [VaccineController::class, 'edit'])->name('admin.vaccines.edit');
            Route::put('/{id}', [VaccineController::class, 'update'])->name('admin.vaccines.update');
            Route::delete('/{id}', [VaccineController::class, 'destroy'])->name('admin.vaccines.destroy');
        });

        Route::get('/community', function () {
            return view('admin.community');
        })->name('admin.community');

        Route::get('/reports', function () {
            return view('admin.reports');
        })->name('admin.reports');

        Route::get('/archives', function () {
            return view('admin.archives');
        })->name('admin.archives');
        // Add your admin dashboard and protected routes here
    });
});

