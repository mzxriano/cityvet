<?php

use App\Http\Controllers\Web\ActivityController;
use App\Http\Controllers\Web\AnimalController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/activities', [ActivityController::class, 'index'])->name('activities');
Route::post('/activities', [ActivityController::class, 'create'])->name('activities.store');

Route::get('/users', [UserController::class, 'index'])->name('users');

Route::get('/animals', [AnimalController::class, 'index'])->name('animals');

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
