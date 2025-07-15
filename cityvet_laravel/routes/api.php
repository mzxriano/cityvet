<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AnimalController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BarangayController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class,'register']);

    Route::get('/barangay', [BarangayController::class,'index']);


    Route::middleware(['auth:api'])->group(function () {
        Route::prefix('user')->group(function () {
            Route::get('/', [UserController::class,'show']);
            Route::post('/edit', [UserController::class,'update']);
        });

        Route::prefix('activity')->group(function () {
            Route::get('/', [ActivityController::class,'index']);
        });

        Route::prefix('animals')->group(function () {
            Route::post('/', [AnimalController::class,'store']);
            Route::get('/', [AnimalController::class,'index']);
            Route::get('/{qrCode}', [AnimalController::class,'showByQrCode']);
            Route::put('/{id}', [AnimalController::class,'update']);
            Route::post('/{id}', [AnimalController::class,'update']);
            Route::delete('/{id}', [AnimalController::class,'destroy']);
        });
        
    }); 

});

