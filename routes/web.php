<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\HomeController;
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

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');
