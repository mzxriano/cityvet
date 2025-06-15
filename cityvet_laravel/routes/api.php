<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
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
