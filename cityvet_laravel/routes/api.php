<?php

use App\Http\Controllers\AnimalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class,'register']);


    Route::middleware(['auth:api'])->group(function () {
        Route::prefix('user')->group(function () {
            Route::get('/', [UserController::class,'show']);
        });

        Route::prefix('animals')->group(function () {
            Route::post('/', [AnimalController::class,'store']);
            Route::get('/', [AnimalController::class,'index']);
        });
    }); 

});

