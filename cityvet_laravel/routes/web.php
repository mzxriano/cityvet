<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureAdminSession;
use App\Http\Controllers\Web\ActivityController;
use App\Http\Controllers\Web\AnimalController;
use App\Http\Controllers\Web\BarangayController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\AdminAuthController;
use App\Http\Controllers\Web\VaccineController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\IncidentController;
use App\Http\Controllers\Web\ArchiveController;


Route::get('/successful-verification', function () {
    return view('mail.verification_successful');
})->name('email.successful');

Route::post('/resend-verification', function (Request $request) {
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user) {
        return back()->withErrors(['email' => 'User not found']);
    }

    if ($user->hasVerifiedEmail()) {
        return back()->withErrors(['email' => 'Email already verified']);
    }

    try {
        \Mail::to($user->email)->send(new \App\Mail\VerifyEmail($user));
        return redirect()->route('email.successful')->with('success', 'Verification email sent successfully!');
    } catch (\Exception $e) {
        return back()->withErrors(['email' => 'Failed to send verification email']);
    }
    })->name('resend.verification');

Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('showLogin');
Route::post('/login', [AdminAuthController::class, 'login'])->name('login');

// Admin Email Verification
Route::prefix('admin')->group(function () {
    Route::get('/email/verify', [App\Http\Controllers\Web\AdminAuthController::class, 'showVerificationNotice'])->name('admin.verification.notice');
    Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\Web\AdminAuthController::class, 'verifyEmail'])->name('admin.verification.verify');
    Route::post('/email/resend', [App\Http\Controllers\Web\AdminAuthController::class, 'resendVerificationEmail'])->name('admin.verification.resend');
});

Route::prefix('admin')->group(function () {

    // Admin Forgot Password
    Route::get('/forgot-password', [App\Http\Controllers\Web\AdminAuthController::class, 'showForgotPasswordForm'])->name('admin.forgot_password');
    Route::post('/forgot-password', [App\Http\Controllers\Web\AdminAuthController::class, 'sendResetLink'])->name('admin.forgot_password.send');
    Route::get('/reset-password/{token}', [App\Http\Controllers\Web\AdminAuthController::class, 'showResetPasswordForm'])->name('admin.reset_password');
    Route::post('/reset-password', [App\Http\Controllers\Web\AdminAuthController::class, 'resetPassword'])->name('admin.reset_password.submit');

    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    Route::middleware(['auth:admin', EnsureAdminSession::class, \App\Http\Middleware\EnsureAdminEmailIsVerified::class])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
        
        // API endpoint for calendar activities
        Route::get('/api/activities/calendar', [DashboardController::class, 'getCalendarActivities'])->name('admin.api.activities.calendar');

        Route::prefix('activities')->group(function () {
            Route::get('/', [ActivityController::class, 'index'])->name('admin.activities');
            Route::get('/pending', [ActivityController::class, 'pendingRequests'])->name('admin.activities.pending');
            Route::get('/{id}/show', [ActivityController::class, 'show'])->name('admin.activities.show');
            Route::get('/{id}/memo', [ActivityController::class, 'downloadMemo'])->name('admin.activities.memo');
            Route::post('/', [ActivityController::class, 'create'])->name('admin.activities.store');
            Route::put('/{id}', [ActivityController::class, 'update'])->name('admin.activities.update');
            Route::post('/{id}/approve', [ActivityController::class, 'approveRequest'])->name('admin.activities.approve');
            Route::post('/{id}/reject', [ActivityController::class, 'rejectRequest'])->name('admin.activities.reject');
            Route::delete('/{id}', [ActivityController::class, 'destroy'])->name('admin.activities.destroy');
        });

        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('admin.users');
            Route::post('/', [UserController::class, 'store'])->name('admin.users.store');
            Route::get('/{id}/show', [UserController::class, 'show'])->name('admin.users.show');
            Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
            Route::patch('/{id}/approve', [UserController::class, 'approve'])->name('admin.users.approve');
            Route::patch('/{id}/reject', [UserController::class, 'reject'])->name('admin.users.reject');
            Route::get('/search', [AnimalController::class,'searchOwner'])->name('search.owner');
        });

        Route::prefix('animals')->group(function () {
            Route::get('/', [AnimalController::class, 'index'])->name('admin.animals');
            Route::get('/batch-register', [AnimalController::class, 'showBatchRegistration'])->name('admin.animals.batch-register');
            Route::post('/batch-store', [AnimalController::class, 'batchStore'])->name('admin.animals.batch-store');
            Route::post('/csv-import', [AnimalController::class, 'csvImport'])->name('admin.animals.csv-import');
            Route::get('/csv-template', [AnimalController::class, 'csvTemplate'])->name('admin.animals.csv-template');
            Route::get('/{id}/show', [AnimalController::class, 'show'])->name('admin.animals.show');
            Route::post('/', [AnimalController::class, 'store'])->name('admin.animals.store');
            Route::put('/{id}', [AnimalController::class, 'update'])->name('admin.animals.update');
        });

        Route::get('/barangay', [BarangayController::class, 'index'])->name('admin.barangay');

        // API endpoints for admin functionality
        Route::prefix('api')->group(function () {
            Route::get('/users/search', [App\Http\Controllers\Web\ApiController::class, 'searchUsers']);
        });

        Route::prefix('vaccines')->group(function () {
            Route::get('/', [VaccineController::class, 'index'])->name('admin.vaccines');
            Route::get('/add', [VaccineController::class, 'create'])->name('admin.vaccines.add');
            Route::get('/{id}/show', [VaccineController::class, 'show'])->name('admin.vaccines.show');
            Route::post('/', [VaccineController::class, 'store'])->name('admin.vaccines.store');
            Route::get('/{id}/edit', [VaccineController::class, 'edit'])->name('admin.vaccines.edit');
            Route::put('/{id}', [VaccineController::class, 'update'])->name('admin.vaccines.update');
            Route::delete('/{id}', [VaccineController::class, 'destroy'])->name('admin.vaccines.destroy');
            Route::patch('/{vaccine}/stock', [VaccineController::class, 'updateStock'])->name('admin.vaccines.stock');
        });


        Route::get('/community', function () {
            return view('admin.community');
        })->name('admin.community');

        // Admin AJAX endpoints for community moderation
        Route::get('/community/pending-posts', [\App\Http\Controllers\Web\CommunityController::class, 'pending'])->name('admin.community.pending');
        Route::patch('/community/{id}/review', [\App\Http\Controllers\Web\CommunityController::class, 'review'])->name('admin.community.review');
        Route::get('/community/approved-posts', [\App\Http\Controllers\Web\CommunityController::class, 'approved'])->name('admin.community.approved');
        Route::get('/community/reported-posts', [\App\Http\Controllers\Web\CommunityController::class, 'reportedPosts'])->name('admin.community.reported-posts');
        Route::get('/community/reported-comments', [\App\Http\Controllers\Web\CommunityController::class, 'reportedComments'])->name('admin.community.reported-comments');

        Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports');
        Route::post('/reports/generate-vaccination', [ReportController::class, 'generateVaccinationReport'])->name('reports.generate-vaccination');
        Route::post('/reports/generate-vaccination-excel', [ReportController::class, 'generateVaccinationExcel'])->name('reports.generate-vaccination-excel');
        Route::post('/reports/generate-bite-case-excel', [ReportController::class, 'generateBiteCaseExcel'])->name('reports.generate-bite-case-excel');

        Route::prefix('archives')->group(function () {
            Route::get('/', [ArchiveController::class, 'index'])->name('admin.archives');
            Route::get('/memorial/{id}', [ArchiveController::class, 'memorial'])->name('admin.archives.memorial');
            Route::get('/record/{id}', [ArchiveController::class, 'record'])->name('admin.archives.record');
            Route::post('/restore/{id}', [ArchiveController::class, 'restore'])->name('admin.archives.restore');
        });

        Route::get('/settings', [App\Http\Controllers\Web\SettingsController::class, 'index'])
            ->name('admin.settings');
        
        Route::post('/settings/password', [App\Http\Controllers\Web\SettingsController::class, 'updatePassword'])
            ->name('settings.password.update');
        
        Route::post('/settings/profile', [App\Http\Controllers\Web\SettingsController::class, 'updateProfile'])
            ->name('settings.profile.update');
        
        Route::post('/settings/theme', [App\Http\Controllers\Web\SettingsController::class, 'updateTheme'])
            ->name('settings.theme.update');
        
        Route::post('/settings/system', [App\Http\Controllers\Web\SettingsController::class, 'updateSettings'])
            ->name('settings.system.update');

        // CMS Routes
        Route::prefix('cms')->group(function () {
            Route::get('/', [App\Http\Controllers\Web\CmsController::class, 'index'])->name('admin.cms');
            Route::get('/animals', [App\Http\Controllers\Web\CmsController::class, 'animals'])->name('admin.cms.animals');
            Route::post('/animals/types', [App\Http\Controllers\Web\CmsController::class, 'storeAnimalType'])->name('admin.cms.animals.types.store');
            Route::delete('/animals/types/{id}', [App\Http\Controllers\Web\CmsController::class, 'deleteAnimalType'])->name('admin.cms.animals.types.delete');
            Route::post('/animals/breeds', [App\Http\Controllers\Web\CmsController::class, 'storeBreed'])->name('admin.cms.animals.breeds.store');
            Route::delete('/animals/breeds/{id}', [App\Http\Controllers\Web\CmsController::class, 'deleteBreed'])->name('admin.cms.animals.breeds.delete');
            Route::get('/users', [App\Http\Controllers\Web\CmsController::class, 'users'])->name('admin.cms.users');
            Route::post('/users/inactivity-threshold', [App\Http\Controllers\Web\CmsController::class, 'updateInactivityThreshold'])->name('admin.cms.users.threshold.update');
        });

        Route::get('/notifications', [\App\Http\Controllers\Web\NotificationController::class, 'index'])->name('admin.notifications');
        Route::get('/api/notifications/recent', [\App\Http\Controllers\Web\NotificationController::class, 'getRecentNotifications'])->name('admin.notifications.recent');
        Route::post('/api/notifications/{id}/read', [\App\Http\Controllers\Web\NotificationController::class, 'markAsRead'])->name('admin.notifications.read');
        Route::post('/api/notifications/mark-all-read', [\App\Http\Controllers\Web\NotificationController::class, 'markAllAsRead'])->name('admin.notifications.mark-all-read');

        Route::get('/bite-case', [IncidentController::class, 'index'])->name('admin.bite-case');
        Route::get('/incidents/{id}', [IncidentController::class, 'show'])->name('admin.incidents.show');
        // Admin can only view incidents, status management is handled by barangay personnel
        
        // Calendar routes
        Route::get('/calendar', [App\Http\Controllers\Web\CalendarController::class, 'index'])->name('admin.calendar');
        Route::get('/calendar/previous', [App\Http\Controllers\Web\CalendarController::class, 'previous'])->name('admin.calendar.previous');
        Route::get('/calendar/next', [App\Http\Controllers\Web\CalendarController::class, 'next'])->name('admin.calendar.next');
        
    });
});

