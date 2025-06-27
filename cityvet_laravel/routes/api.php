<?php

use App\Http\Controllers\AnimalController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('/users', function () {
    return response()->json([
        ['id' => 'bene', 'name' => 'Test User 1'],
        ['id' => 'dict', 'name' => 'Test User 2'],
    ]);
});

Route::post('/register', [AuthController::class,'register']);
Route::post('/login', [AuthController::class,'login']);
Route::post('/create-animal', [AnimalController::class,'store']);
Route::get('/animals', [AnimalController::class,'index']);
