<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::get('settings', function () {
    return Inertia::render('settings/Index');
})->name('settings.index');

Route::redirect('settings/profile', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('settings.profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('settings.profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('settings.profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance');

    // System Settings
    Route::get('settings/system', function () {
        return Inertia::render('settings/System');
    })->name('settings.system');

    // Features
    Route::get('settings/features', function () {
        return Inertia::render('settings/Features');
    })->name('settings.features');

    // Billing & Subscriptions
    Route::get('settings/billing', function () {
        return Inertia::render('settings/Billing');
    })->name('settings.billing');

    // Payment Types
    Route::get('settings/payment-types', [\App\Http\Controllers\Settings\PaymentTypesController::class, 'index'])->name('settings.payment-types');
    Route::post('settings/payment-types', [\App\Http\Controllers\Settings\PaymentTypesController::class, 'store'])->name('settings.payment-types.store');
    Route::post('settings/payment-types/test-mpesa', [\App\Http\Controllers\Settings\PaymentTypesController::class, 'testMpesaCredentials'])->name('settings.payment-types.test-mpesa');
    Route::post('settings/payment-types/save-mpesa', [\App\Http\Controllers\Settings\PaymentTypesController::class, 'saveMpesaCredentials'])->name('settings.payment-types.save-mpesa');
    Route::post('settings/payment-types/get-branches', [\App\Http\Controllers\Settings\PaymentTypesController::class, 'getBranches'])->name('settings.payment-types.get-branches');
    Route::post('settings/payment-types/select-business', [\App\Http\Controllers\Settings\PaymentTypesController::class, 'selectBusiness'])->name('settings.payment-types.select-business');
    Route::post('settings/payment-types/select-branch', [\App\Http\Controllers\Settings\PaymentTypesController::class, 'selectBranch'])->name('settings.payment-types.select-branch');
Route::post('settings/payment-types/get-credentials', [\App\Http\Controllers\Settings\PaymentTypesController::class, 'getCredentials'])->name('settings.payment-types.get-credentials');

Route::get('settings/payment-types/callback-urls', [\App\Http\Controllers\Settings\PaymentTypesController::class, 'getCallbackUrls'])->name('settings.payment-types.callback-urls');
Route::put('settings/payment-types/callback-urls/{id}', [\App\Http\Controllers\Settings\PaymentTypesController::class, 'updateCallbackUrl'])->name('settings.payment-types.update-callback-url');
Route::get('settings/payment-types/callback-responses', [\App\Http\Controllers\Settings\PaymentTypesController::class, 'getCallbackResponses'])->name('settings.payment-types.callback-responses');
Route::post('settings/payment-types/query-payment-status', [\App\Http\Controllers\Settings\PaymentTypesController::class, 'queryPaymentStatus'])->name('settings.payment-types.query-payment-status');
Route::post('settings/payment-types/clear-callback-cache', [\App\Http\Controllers\Settings\PaymentTypesController::class, 'clearCallbackCache'])->name('settings.payment-types.clear-callback-cache');
});

// M-PESA Callback Route (outside auth middleware - external webhook)
Route::post('settings/payment-types/callback', [\App\Http\Controllers\Settings\PaymentTypesController::class, 'callback'])->name('settings.payment-types.callback');

Route::middleware('auth')->group(function () {
    // Loyalty
    Route::get('settings/loyalty', function () {
        return Inertia::render('settings/Loyalty');
    })->name('settings.loyalty');

    // Taxes
    Route::get('settings/taxes', function () {
        return Inertia::render('settings/Taxes');
    })->name('settings.taxes');

    // Receipt
    Route::get('settings/receipt', function () {
        return Inertia::render('settings/Receipt');
    })->name('settings.receipt');

    // Open Tickets
    Route::get('settings/tickets', function () {
        return Inertia::render('settings/Tickets');
    })->name('settings.tickets');

    // Kitchen Printers
    Route::get('settings/kitchen-printers', function () {
        return Inertia::render('settings/KitchenPrinters');
    })->name('settings.kitchen-printers');

    // Dining Options
    Route::get('settings/dining-options', function () {
        return Inertia::render('settings/DiningOptions');
    })->name('settings.dining-options');

    // Location & POS Settings
    Route::get('settings/location-pos', function () {
        return Inertia::render('settings/LocationPos');
    })->name('settings.location-pos');

    // Locations Management
    Route::get('settings/locations/create', [\App\Http\Controllers\Settings\LocationController::class, 'create'])->name('settings.locations.create');
    Route::get('settings/locations/{location}/edit', [\App\Http\Controllers\Settings\LocationController::class, 'edit'])->name('settings.locations.edit');
    Route::get('settings/locations', [\App\Http\Controllers\Settings\LocationController::class, 'index'])->name('settings.locations');
    Route::post('settings/locations', [\App\Http\Controllers\Settings\LocationController::class, 'store'])->name('settings.locations.store');
    Route::put('settings/locations/{location}', [\App\Http\Controllers\Settings\LocationController::class, 'update'])->name('settings.locations.update');
    Route::delete('settings/locations/{location}', [\App\Http\Controllers\Settings\LocationController::class, 'destroy'])->name('settings.locations.destroy');
    Route::get('settings/locations/api/list', [\App\Http\Controllers\Settings\LocationController::class, 'getLocations'])->name('settings.locations.api');

    // Location Types Management
    Route::post('settings/location-types', [\App\Http\Controllers\Settings\LocationTypeController::class, 'store'])->name('settings.location-types.store');
});
