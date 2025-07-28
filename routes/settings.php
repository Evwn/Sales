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
    Route::get('settings/payment-types', function () {
        return Inertia::render('settings/PaymentTypes');
    })->name('settings.payment-types');

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
