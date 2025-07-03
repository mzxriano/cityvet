<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/activities', [ActivityController::class, 'index'])->name('activities');

Route::get('/users', [UserController::class, 'index'])->name('users');

Route::get('/animals', [AnimalController::class, 'indexWeb'])->name('animals');

Route::get('/barangay', function () {
    return view('barangay');
})->name('barangay');

Route::get('/vaccines', function () {
    return view('vaccines');
})->name('vaccines');

Route::get('/reports', function () {
    return view('reports');
})->name('reports');

Route::get('/archives', function () {
    return view('archives');
})->name('archives');

Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
