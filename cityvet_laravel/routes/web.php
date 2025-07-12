<?php

use App\Http\Controllers\Web\ActivityController;
use App\Http\Controllers\Web\AnimalController;
use App\Http\Controllers\Web\BarangayController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\DashboardController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/activities', [ActivityController::class, 'index'])->name('activities');
Route::post('/activities', [ActivityController::class, 'create'])->name('activities.store');

Route::get('/users', [UserController::class, 'index'])->name('users');

Route::get('/animals', [AnimalController::class, 'index'])->name('animals');
Route::post('/animals', [AnimalController::class, 'store'])->name('animals.store');
Route::put('/animals/{id}', [AnimalController::class, 'update'])->name('animals.update');

Route::get('/barangay', [BarangayController::class, 'index'])->name('barangay');

Route::get('/vaccines', function () {
    return view('vaccines');
})->name('vaccines');

Route::get('/community', function () {
    return view('community');
})->name('community');

Route::get('/reports', function () {
    return view('reports');
})->name('reports');

Route::get('/archives', function () {
    return view('archives');
})->name('archives');

Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::put('/users/{id}', [UserController::class, 'edit'])->name('users.edit');
Route::get('/users/search', [AnimalController::class,'searchOwner'])->name('search.owner');