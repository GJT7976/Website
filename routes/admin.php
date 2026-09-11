<?php

use App\Http\Controllers\Admin\AppController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PageContentController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SupportRequestController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// This file is loaded with the "admin." name prefix and "/admin" path
// prefix already applied in bootstrap/app.php.

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
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

    Route::resource('media', MediaController::class)
        ->except(['show', 'edit', 'create'])
        ->parameters(['media' => 'media']); // keep {media}, not the auto-singularized {medium}

    Route::get('content', [PageContentController::class, 'index'])->name('content.index');
    Route::get('content/{page:slug}', [PageContentController::class, 'edit'])->name('content.edit');
    Route::put('content/{page:slug}', [PageContentController::class, 'update'])->name('content.update');

    Route::resource('faqs', FaqController::class)->except(['show']);

    Route::resource('support', SupportRequestController::class)->only(['index', 'show', 'update']);

    Route::middleware('role:owner')->group(function () {
        Route::get('settings/{group}', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings/{group}', [SettingController::class, 'update'])->name('settings.update');
        Route::resource('users', UserController::class)->except(['show']);
    });
});
