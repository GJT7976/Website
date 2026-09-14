<?php

use App\Http\Controllers\Api\LicenseController;
use Illuminate\Support\Facades\Route;

// Registered via the `api:` entry in bootstrap/app.php, so this gets
// Laravel's stateless "api" middleware group (no CSRF/session) — what a
// Flutter client calling over HTTPS needs. See LICENSE_SYSTEM.md.

Route::prefix('license')->name('api.license.')->group(function () {
    Route::post('activate', [LicenseController::class, 'activate'])
        ->middleware('throttle:20,1')
        ->name('activate');

    Route::post('validate', [LicenseController::class, 'validateLicense'])
        ->middleware('throttle:30,1')
        ->name('validate');

    Route::post('deactivate', [LicenseController::class, 'deactivate'])
        ->middleware('throttle:10,1')
        ->name('deactivate');

    Route::post('manage/request-code', [LicenseController::class, 'requestManagementCode'])
        ->middleware('throttle:5,1')
        ->name('manage.request-code');

    Route::post('manage/verify-code', [LicenseController::class, 'verifyManagementCode'])
        ->middleware('throttle:10,1')
        ->name('manage.verify-code');
});
