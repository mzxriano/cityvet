<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AnimalController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class,'register']);


    Route::middleware(['auth:api'])->group(function () {
        Route::prefix('user')->group(function () {
            Route::get('/', [UserController::class,'show']);
        });

        Route::prefix('activity')->group(function () {
            Route::get('/', [ActivityController::class,'index']);
        });

        Route::prefix('animals')->group(function () {
            Route::post('/', [AnimalController::class,'store']);
            Route::get('/', [AnimalController::class,'index']);
        });
    }); 

});

