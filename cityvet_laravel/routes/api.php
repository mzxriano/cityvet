<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AnimalController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BarangayController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CommunityController;
use App\Http\Controllers\Api\CommunityCommentController;
use App\Http\Controllers\Api\CommunityLikeController;
use Illuminate\Support\Facades\Route;

Route::get('/verify-email/{id}', [AuthController::class, 'verifyEmail']);

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class,'register']);

    Route::post('/resend-verification', [AuthController::class, 'resendVerification']);

    // Password reset (OTP)
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

    Route::get('/barangay', [BarangayController::class,'index']);

    Route::middleware(['auth:api'])->group(function () {
        // User
        Route::prefix('user')->group(function () {
            Route::get('/', [UserController::class,'show']);
            Route::post('/edit', [UserController::class,'update']);
            Route::post('/logout', [AuthController::class,'logout']);
        });

        // Activity
        Route::prefix('activity')->group(function () {
            Route::get('/', [ActivityController::class,'index']);
        });
        
        // Animals 
        Route::prefix('animals')->group(function () {
            Route::post('/', [AnimalController::class,'store']);
            Route::get('/', [AnimalController::class,'index']);
            Route::get('/{qrCode}', [AnimalController::class,'showByQrCode']);
            Route::put('/{id}', [AnimalController::class,'update']);
            Route::post('/{id}', [AnimalController::class,'update']);
            Route::delete('/{id}', [AnimalController::class,'destroy']);
            Route::post('/{animal}/vaccines', [AnimalController::class, 'attachVaccines']);
        });
        // Vaccines
        Route::get('/vaccines', [\App\Http\Controllers\Api\VaccineController::class, 'index']);
        
        // Community Engagement
        Route::get('/community', [CommunityController::class, 'index']);
        Route::post('/community', [CommunityController::class, 'store']);
        Route::get('/community/{id}', [CommunityController::class, 'show']);
        Route::delete('/community/{id}', [CommunityController::class, 'destroy']);

        Route::post('/community/{id}/comment', [CommunityCommentController::class, 'store']);
        Route::get('/community/{id}/comments', [CommunityCommentController::class, 'index']);

        Route::post('/community/{id}/like', [CommunityLikeController::class, 'toggle']);
    }); 

});

