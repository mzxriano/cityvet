<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AnimalController;
use App\Http\Controllers\Api\AnimalTypeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BarangayController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CommunityController;
use App\Http\Controllers\Api\CommunityCommentController;
use App\Http\Controllers\Api\CommunityLikeController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\VaccineController;
use App\Http\Controllers\Api\IncidentController;

Route::get('/verify-email/{id}', [AuthController::class, 'verifyEmail']);

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class,'register']);

    Route::middleware(['auth:api'])->group(function () {
        // Register Animal Owner (Admin/Staff only)
        Route::post('/register-owner', [AuthController::class, 'registerOwner']);
    });

    // Password reset (OTP)
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

    Route::get('/barangay', [BarangayController::class,'index']);

    Route::post('/resend-verification', [AuthController::class, 'resendVerification']);

    Route::middleware(['auth:api'])->group(function () {

    Route::post('/force-change-password', [UserController::class, 'changePassword']);

        // User
        Route::prefix('user')->group(function () {
            Route::get('/', [UserController::class,'show']);
            Route::post('/edit', [UserController::class,'update']);
            Route::post('/logout', [AuthController::class,'logout']);
        });

        // Activity
        Route::prefix('activity')->group(function () {
            Route::get('/upcoming', [ActivityController::class,'index']);
            Route::get('/ongoing', [ActivityController::class,'ongoingActivity']);
            Route::get('/recent', [ActivityController::class,'recentActivities']);
            Route::get('/vaccinated-animals', [ActivityController::class,'getVaccinatedAnimals']);
            Route::get('/{activityId}/vaccinated-animals', [ActivityController::class,'getVaccinatedAnimalsByActivity']);
            Route::post('/{id}/upload-images', [ActivityController::class,'uploadImages']);
        });

        // Activity Requests (for AEW users)
        Route::post('/activities/request', [ActivityController::class, 'submitRequest']);

        Route::get('/recent-activities', [ActivityController::class,'recentActivities']);

        Route::get('/vaccination-records', [VaccineController::class,'getAllVaccinationRecords']);

        
        // Animals 
        Route::prefix('animals')->group(function () {
            Route::post('/', [AnimalController::class,'store']);
            Route::get('/', [AnimalController::class,'index']);
            Route::get('/all', [AnimalController::class,'fetchAllAnimals']);
            Route::post('/add-for-owner', [AnimalController::class,'addAnimalForOwner']);
            
            // Specific routes must come BEFORE parameterized routes
            Route::get('/archived', [AnimalController::class,'getArchivedAnimals']);
            Route::post('/{id}/archive', [AnimalController::class,'archiveAnimal']);
            Route::post('/archived/{archiveId}/restore', [AnimalController::class,'restoreArchivedAnimal']);
            
            // Parameterized routes come last
            Route::get('/{qrCode}', [AnimalController::class,'showByQrCode']);
            Route::put('/{id}', [AnimalController::class,'update']);
            Route::post('/{id}', [AnimalController::class,'update']);
            Route::delete('/{id}', [AnimalController::class,'destroy']);
            Route::post('/{animalId}/vaccines', [AnimalController::class, 'attachVaccines']);
            Route::post('/activity/{activityId}/vaccinate', [AnimalController::class, 'attachVaccinesToActivity']);
        });

        // Search Owners
        Route::get('/search-owners', [AnimalController::class, 'searchOwners']);

        // Vaccines
        Route::get('/vaccines', [VaccineController::class, 'index']);

        // Fetch Veterinarians
        Route::get('/veterinarians', [VaccineController::class, 'fetchVeterinarians']);

        // Fetch Aews
        Route::get('/aew', [UserController::class, 'fetchAew']);
        
        // Community Engagement
        Route::get('/community', [CommunityController::class, 'index']);
        Route::get('/community/user', [CommunityController::class, 'getUserPosts']);
        Route::post('/community', [CommunityController::class, 'store']);
        Route::get('/community/{id}', [CommunityController::class, 'show']);
        Route::patch('/community/{id}', [CommunityController::class, 'update']);
        Route::delete('/community/{id}', [CommunityController::class, 'destroy']);
        Route::post('/community/{id}/comment', [CommunityCommentController::class, 'store']);
        Route::get('/community/{id}/comments', [CommunityCommentController::class, 'index']);
        Route::post('/community/{id}/like', [CommunityLikeController::class, 'toggle']);
        Route::post('/community/{id}/report', [CommunityController::class, 'reportPost']);
        Route::post('/community/comment/{commentId}/report', [CommunityController::class, 'reportComment']);
        
        // Admin Community Management
        Route::get('/admin/community/pending', [CommunityController::class, 'getPendingPosts']);
        Route::patch('/admin/community/{id}/review', [CommunityController::class, 'reviewPost']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/send-notification', [NotificationController::class, 'sendPushNotification']);
        Route::post('/save-device-token', [DeviceTokenController::class, 'save']);
        
        // Incident Reporting
        Route::prefix('incidents')->group(function () {
            Route::get('/', [IncidentController::class, 'index']);
            Route::post('/', [IncidentController::class, 'store']);
            Route::get('/statistics', [IncidentController::class, 'statistics']);
            Route::get('/{id}', [IncidentController::class, 'show']);
            Route::put('/{id}', [IncidentController::class, 'update']);
            Route::put('/{id}/status', [IncidentController::class, 'updateStatus']);
            Route::delete('/{id}', [IncidentController::class, 'destroy']);
        });

        // Animal Types and Breeds Management
        Route::prefix('animal-types')->group(function () {
            Route::get('/', [AnimalTypeController::class, 'index']);
            Route::get('/{id}/breeds', [AnimalTypeController::class, 'getBreeds']);
            Route::get('/by-name/{typeName}/breeds', [AnimalTypeController::class, 'getBreedsByTypeName']);
            
            // Admin-only routes for managing animal types and breeds
            Route::middleware(['role:admin'])->group(function () {
                Route::post('/', [AnimalTypeController::class, 'store']);
                Route::put('/{id}', [AnimalTypeController::class, 'update']);
                Route::delete('/{id}', [AnimalTypeController::class, 'destroy']);
                
                // Breed management
                Route::post('/{typeId}/breeds', [AnimalTypeController::class, 'storeBreed']);
                Route::put('/{typeId}/breeds/{breedId}', [AnimalTypeController::class, 'updateBreed']);
                Route::delete('/{typeId}/breeds/{breedId}', [AnimalTypeController::class, 'destroyBreed']);
            });
        });
    }); 

});

