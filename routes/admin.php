<?php

use App\Http\Controllers\Admin\AppController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EditionController;
use App\Http\Controllers\Admin\EntitlementController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageContentController;
use App\Http\Controllers\Admin\ReleaseController;
use App\Http\Controllers\Admin\SalesController;
use App\Http\Controllers\Admin\SeedController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SupportRequestController;
use App\Http\Controllers\Admin\TaxRuleController;
use App\Http\Controllers\Admin\TwoFactorChallengeController;
use App\Http\Controllers\Admin\TwoFactorSettingsController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// This file is loaded with the "admin." name prefix and "/admin" path
// prefix already applied in bootstrap/app.php.

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');

    // Second login step for a user with hasTwoFactorEnabled() — reached
    // only via the pending-session marker AuthController@store sets.
    Route::get('two-factor-challenge', [TwoFactorChallengeController::class, 'create'])->name('two-factor.challenge');
    Route::post('two-factor-challenge', [TwoFactorChallengeController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('two-factor.challenge.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('apps', AppController::class);
    Route::post('apps/{app}/publish', [AppController::class, 'publish'])->name('apps.publish');
    Route::post('apps/{app}/unpublish', [AppController::class, 'unpublish'])->name('apps.unpublish');
    Route::post('apps/{app}/archive', [AppController::class, 'archive'])->name('apps.archive');
    Route::post('apps/{app}/feature', [AppController::class, 'toggleFeature'])->name('apps.feature');
    Route::post('apps/{app}/duplicate', [AppController::class, 'duplicate'])->name('apps.duplicate');
    Route::post('apps/{app}/media', [AppController::class, 'attachMedia'])->name('apps.media.attach');
    Route::delete('apps/{app}/media/{media}', [AppController::class, 'detachMedia'])->name('apps.media.detach');
    Route::post('apps/{app}/features', [AppController::class, 'addFeature'])->name('apps.features.store');
    Route::delete('apps/{app}/features/{feature}', [AppController::class, 'removeFeature'])->name('apps.features.destroy');

    Route::post('apps/{app}/editions', [EditionController::class, 'store'])->name('apps.editions.store');
    Route::put('apps/{app}/editions/{edition}', [EditionController::class, 'update'])->name('apps.editions.update');
    Route::delete('apps/{app}/editions/{edition}', [EditionController::class, 'destroy'])->name('apps.editions.destroy');
    Route::post('apps/{app}/editions/{edition}/entitlements', [EditionController::class, 'syncEntitlements'])->name('apps.editions.entitlements');

    Route::post('apps/{app}/releases', [ReleaseController::class, 'store'])->name('apps.releases.store');
    Route::post('apps/{app}/releases/{release}/activate', [ReleaseController::class, 'activate'])->name('apps.releases.activate');
    Route::delete('apps/{app}/releases/{release}', [ReleaseController::class, 'destroy'])->name('apps.releases.destroy');

    Route::post('apps/{app}/entitlements', [EntitlementController::class, 'store'])->name('apps.entitlements.store');
    Route::post('entitlements/{entitlement}/revoke', [EntitlementController::class, 'revoke'])->name('entitlements.revoke');
    Route::post('entitlements/{entitlement}/restore', [EntitlementController::class, 'restore'])->name('entitlements.restore');

    Route::resource('media', MediaController::class)
        ->except(['show', 'edit', 'create'])
        ->parameters(['media' => 'media']); // keep {media}, not the auto-singularized {medium}

    Route::get('content', [PageContentController::class, 'index'])->name('content.index');
    Route::get('content/{page:slug}', [PageContentController::class, 'edit'])->name('content.edit');
    Route::put('content/{page:slug}', [PageContentController::class, 'update'])->name('content.update');

    Route::resource('faqs', FaqController::class)->except(['show']);

    Route::resource('support', SupportRequestController::class)->only(['index', 'show', 'update']);

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/resend-receipt', [OrderController::class, 'resendReceipt'])->name('orders.resend-receipt');

    Route::get('sales', [SalesController::class, 'index'])->name('sales.index');
    Route::get('sales/export', [SalesController::class, 'export'])->name('sales.export');

    Route::get('licenses', [LicenseController::class, 'index'])->name('licenses.index');
    Route::get('licenses/{license}', [LicenseController::class, 'show'])->name('licenses.show');
    Route::post('licenses/{license}/reset-activations', [LicenseController::class, 'resetActivations'])->name('licenses.reset-activations');
    Route::post('licenses/{license}/revoke', [LicenseController::class, 'revoke'])->name('licenses.revoke');
    Route::post('licenses/{license}/restore', [LicenseController::class, 'restore'])->name('licenses.restore');
    Route::post('licenses/{license}/mark-refunded', [LicenseController::class, 'markRefunded'])->name('licenses.mark-refunded');
    Route::post('licenses/{license}/resend-email', [LicenseController::class, 'resendEmail'])->name('licenses.resend-email');
    Route::post('license-devices/{device}/deactivate', [LicenseController::class, 'deactivateDevice'])->name('license-devices.deactivate');

    // Self-service 2FA enrollment for one's own account — any authenticated
    // admin, not owner-only (the spec recommends but doesn't mandate 2FA).
    Route::get('profile/two-factor', [TwoFactorSettingsController::class, 'edit'])->name('two-factor.edit');
    Route::post('profile/two-factor/enable', [TwoFactorSettingsController::class, 'enable'])->name('two-factor.enable');
    Route::post('profile/two-factor/confirm', [TwoFactorSettingsController::class, 'confirm'])->name('two-factor.confirm');
    Route::post('profile/two-factor/disable', [TwoFactorSettingsController::class, 'disable'])->name('two-factor.disable');
    Route::get('profile/two-factor/recovery-codes', [TwoFactorSettingsController::class, 'recoveryCodes'])->name('two-factor.recovery-codes');
    Route::post('profile/two-factor/recovery-codes', [TwoFactorSettingsController::class, 'regenerateRecoveryCodes'])->name('two-factor.recovery-codes.regenerate');

    Route::middleware('role:owner')->group(function () {
        Route::post('orders/{order}/refund', [OrderController::class, 'refund'])->name('orders.refund');

        // Owner override — see UserController::disableTwoFactor()'s docblock.
        Route::post('users/{user}/two-factor/disable', [UserController::class, 'disableTwoFactor'])->name('users.two-factor.disable');

        Route::get('settings/{group}', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings/{group}', [SettingController::class, 'update'])->name('settings.update');

        Route::get('maintenance', [MaintenanceController::class, 'edit'])->name('maintenance.edit');
        Route::post('maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
        Route::delete('maintenance', [MaintenanceController::class, 'destroy'])->name('maintenance.destroy');

        Route::get('seed', [SeedController::class, 'edit'])->name('seed.edit');
        Route::post('seed', [SeedController::class, 'store'])->name('seed.store');
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('tax-rules', TaxRuleController::class)->except(['show']);

        // Read-only on purpose — see AuditLogController's docblock. Never
        // add a store/update/destroy route here.
        Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');

        // Individual routes, not Route::resource — an immutable backup
        // archive has no create/edit form.
        Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('backups', [BackupController::class, 'store'])->name('backups.store');
        Route::get('backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::delete('backups/{backup}', [BackupController::class, 'destroy'])->name('backups.destroy');
    });
});
