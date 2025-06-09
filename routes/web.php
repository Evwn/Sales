<?php

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\BusinessAccess;
use App\Http\Middleware\CheckBranchAccess;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Redirect /profile to /settings/profile
    Route::redirect('/profile', '/settings/profile');

    // Main navigation routes
    Route::get('/branches', [BranchController::class, 'all'])->name('branches.all');
    Route::get('/sellers', [SellerController::class, 'all'])->name('sellers.all');
    Route::get('/sales', [SaleController::class, 'all'])->name('sales.all');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/discounts', [DiscountController::class, 'index'])->name('discounts.index');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    
    // Products - redirect to first business or show message if no businesses
    Route::get('/products', [ProductController::class, 'all'])->name('products.all');

    // Inventory Items Management (Central Catalog)
    Route::get('/inventory-items/search', [InventoryItemController::class, 'search'])->name('inventory-items.search');
    Route::resource('inventory-items', InventoryItemController::class)->except(['destroy']);

    // Business routes
    Route::prefix('businesses')->group(function () {
        Route::get('/', [BusinessController::class, 'index'])->name('businesses.index');
        Route::get('/create', [BusinessController::class, 'create'])->name('businesses.create');
        Route::post('/', [BusinessController::class, 'store'])->name('businesses.store');
        
        Route::middleware(BusinessAccess::class)->group(function () {
            Route::get('/{business}', [BusinessController::class, 'show'])->name('businesses.show');
            Route::get('/{business}/edit', [BusinessController::class, 'edit'])->name('businesses.edit');
            Route::put('/{business}', [BusinessController::class, 'update'])->name('businesses.update');
            Route::delete('/{business}', [BusinessController::class, 'destroy'])->name('businesses.destroy');
            Route::post('/{business}/admins', [BusinessController::class, 'addAdmin'])->name('businesses.addAdmin');
            Route::delete('/{business}/admins/{user}', [BusinessController::class, 'removeAdmin'])->name('businesses.removeAdmin');

            // Product routes
            Route::prefix('{business}/products')->middleware(['auth', 'verified'])->group(function () {
                Route::get('/', [ProductController::class, 'index'])->name('products.index');
                Route::get('/create', [ProductController::class, 'create'])->name('products.create');
                Route::post('/', [ProductController::class, 'store'])->name('products.store');
                Route::get('/{product}', [ProductController::class, 'show'])->name('products.show');
                Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
                Route::put('/{product}', [ProductController::class, 'update'])->name('products.update');
                Route::delete('/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
            });

            // Branch routes
            Route::prefix('{business}/branches')->group(function () {
                Route::get('/', [BranchController::class, 'index'])->name('branches.index');
                Route::get('/create', [BranchController::class, 'create'])->name('branches.create');
                Route::post('/', [BranchController::class, 'store'])->name('branches.store');
                
                Route::middleware(CheckBranchAccess::class)->group(function () {
                    Route::get('/{branch}', [BranchController::class, 'show'])->name('branches.show');
                    Route::get('/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
                    Route::put('/{branch}', [BranchController::class, 'update'])->name('branches.update');
                    Route::delete('/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');

                    // Seller routes
                    Route::prefix('{branch}/sellers')->group(function () {
                        Route::get('/', [SellerController::class, 'index'])->name('sellers.index');
                        Route::get('/create', [SellerController::class, 'create'])->name('sellers.create');
                        Route::post('/', [SellerController::class, 'store'])->name('sellers.store');
                        Route::get('/{seller}', [SellerController::class, 'show'])->name('sellers.show');
                        Route::get('/{seller}/edit', [SellerController::class, 'edit'])->name('sellers.edit');
                        Route::put('/{seller}', [SellerController::class, 'update'])->name('sellers.update');
                        Route::delete('/{seller}', [SellerController::class, 'destroy'])->name('sellers.destroy');
                    });

                    // Sales routes
                    Route::prefix('{branch}/sales')->group(function () {
                        Route::get('/', [SaleController::class, 'index'])->name('sales.index');
                        Route::get('/create', [SaleController::class, 'create'])->name('sales.create');
                        Route::post('/', [SaleController::class, 'store'])->name('sales.store');
                        Route::get('/{sale}', [SaleController::class, 'show'])->name('sales.show');
                        Route::get('/{sale}/edit', [SaleController::class, 'edit'])->name('sales.edit');
                        Route::put('/{sale}', [SaleController::class, 'update'])->name('sales.update');
                        Route::delete('/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');
                    });
                });
            });
        });
    });
});

// Inventory Items
Route::middleware(['auth'])->group(function () {
    Route::get('/api/inventory/search', [InventoryItemController::class, 'search'])->name('inventory.search');
});

// Products
Route::middleware(['auth'])->group(function () {
    Route::get('/businesses/{business}/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/businesses/{business}/products', [ProductController::class, 'store'])->name('products.store');
    Route::delete('/businesses/{business}/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
