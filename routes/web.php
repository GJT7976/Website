<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LicenseManagementController;
use App\Http\Controllers\MyDownloadsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SupportController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/apps', [AppController::class, 'index'])->name('apps.index');
Route::get('/apps/{app:slug}', [AppController::class, 'show'])->name('apps.show');

Route::get('/demos', [DemoController::class, 'index'])->name('demos.index');
Route::get('/demo/{app:slug}', [DemoController::class, 'show'])->name('demos.show');

Route::get('/pricing', PricingController::class)->name('pricing');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/refunds', [PageController::class, 'refunds'])->name('refunds');

Route::get('/support', SupportController::class)->name('support');
Route::get('/support/install/android', [PageController::class, 'installAndroid'])->name('support.install.android');
Route::get('/support/install/windows', [PageController::class, 'installWindows'])->name('support.install.windows');

Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::get('/checkout/{app:slug}', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout/{app:slug}', [CheckoutController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('checkout.store');
Route::get('/checkout/{app:slug}/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/{app:slug}/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

// scopeBindings() so {edition:slug} resolves against $app->editions()
// rather than any AppEdition sharing that slug across other apps.
Route::scopeBindings()->group(function () {
    Route::get('/checkout/{app:slug}/edition/{edition:slug}', [CheckoutController::class, 'create'])->name('checkout.create.edition');
    Route::post('/checkout/{app:slug}/edition/{edition:slug}', [CheckoutController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('checkout.store.edition');
});

Route::get('/my-downloads', [MyDownloadsController::class, 'create'])->name('my-downloads.request');
Route::post('/my-downloads', [MyDownloadsController::class, 'sendLink'])
    ->middleware('throttle:5,1')
    ->name('my-downloads.send-link');
Route::get('/my-downloads/{email}', [MyDownloadsController::class, 'show'])
    ->middleware('signed')
    ->name('my-downloads.show');

Route::get('/downloads/{entitlement}', [DownloadController::class, 'download'])
    ->middleware('signed')
    ->name('downloads.show');

// §10: self-service license device management — email + license key,
// then a one-time code, mirroring My Downloads' no-account pattern.
Route::get('/license/manage', [LicenseManagementController::class, 'requestForm'])->name('license.manage.request');
Route::post('/license/manage', [LicenseManagementController::class, 'sendCode'])
    ->middleware('throttle:5,1')
    ->name('license.manage.send-code');
Route::get('/license/manage/verify', [LicenseManagementController::class, 'verifyForm'])->name('license.manage.verify-form');
Route::post('/license/manage/verify', [LicenseManagementController::class, 'verifyCode'])
    ->middleware('throttle:10,1')
    ->name('license.manage.verify');
Route::get('/license/manage/{license}', [LicenseManagementController::class, 'show'])
    ->middleware('signed')
    ->name('license.manage.show');
Route::post('/license/manage/{license}/devices/{device}/deactivate', [LicenseManagementController::class, 'deactivate'])
    ->middleware(['signed', 'throttle:10,1'])
    ->name('license.manage.deactivate');

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');
