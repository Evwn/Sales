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
use App\Http\Controllers\AppearanceController;
use App\Http\Controllers\ProfileController;
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

Route::middleware(['auth', 'verified', \App\Http\Middleware\RoleRouteAccess::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Main navigation routes
    Route::get('/branches', [BranchController::class, 'all'])->name('branches.all');
    Route::get('/sellers', [SellerController::class, 'all'])->name('sellers.all');
    Route::get('/sales', [SaleController::class, 'all'])->name('sales.all');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/discounts', [DiscountController::class, 'index'])->name('discounts.index');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    
    // Products - redirect to first business or show message if no businesses
    Route::get('/products', [ProductController::class, 'all'])->name('products.all');

    // Inventory Items Management (Central Catalog)
    Route::get('/inventory-items/search', [InventoryItemController::class, 'search'])->name('inventory-items.search');
    Route::resource('inventory-items', InventoryItemController::class)->except(['destroy']);

    // Business routes
    Route::prefix('businesses')->middleware([\App\Http\Middleware\RoleRouteAccess::class])->group(function () {
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
            Route::prefix('{branch}/products')->middleware(['auth', 'verified'])->group(function () {
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
                        Route::post('/', [SaleController::class, 'store'])->name('sales.branch.store');
                        Route::get('/{sale}', [SaleController::class, 'show'])->name('sales.show');
                        Route::get('/{sale}/edit', [SaleController::class, 'edit'])->name('sales.edit');
                        Route::put('/{sale}', [SaleController::class, 'update'])->name('sales.update');
                        Route::delete('/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');
                    });
                });
            });
        });
    });

    Route::get('/settings/appearance', [AppearanceController::class, 'edit'])->name('settings.appearance');
    Route::post('/settings/appearance', [AppearanceController::class, 'update'])->name('settings.appearance.update');

    // Branch routes
    Route::resource('businesses.branches', BranchController::class);
    Route::post('businesses/{business}/branches/{branch}/generate-barcode', [BranchController::class, 'generateBarcode'])->name('branches.generate-barcode');
    Route::get('businesses/{business}/branches/{branch}/download-barcode', [BranchController::class, 'downloadBarcode'])->name('branches.download-barcode');
    Route::get('businesses/{business}/branches/{branch}/print-barcode', [BranchController::class, 'printBarcode'])->name('branches.print-barcode');

    // Receipt History (Owner Only)
    Route::get('/sales/receipt-history', [SaleController::class, 'receiptHistory'])
        ->name('sales.receipt-history')
        ->middleware('role:owner');

    // PDF Export Routes (Owner Only)
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/sales/{sale}/receipt/pdf', [SaleController::class, 'exportReceiptPDF'])->name('sales.receipt.pdf');
        Route::get('/sales/report/pdf', [SaleController::class, 'exportSalesReportPDF'])->name('sales.report.pdf');
        Route::get('/sales/daily-report/pdf', [SaleController::class, 'exportDailyReportPDF'])->name('sales.daily-report.pdf');
        Route::get('/sales/weekly-report/pdf', [SaleController::class, 'exportWeeklyReportPDF'])->name('sales.weekly-report.pdf');
        Route::get('/sales/monthly-report/pdf', [SaleController::class, 'exportMonthlyReportPDF'])->name('sales.monthly-report.pdf');
        Route::get('/sales/quarterly-report/pdf', [SaleController::class, 'exportQuarterlyReportPDF'])->name('sales.quarterly-report.pdf');
        Route::get('/sales/yearly-report/pdf', [SaleController::class, 'exportYearlyReportPDF'])->name('sales.yearly-report.pdf');
        Route::post('/sales/batch-receipts/pdf', [SaleController::class, 'exportBatchReceiptsPDF'])->name('sales.batch-receipts.pdf');
    });
});

// Inventory Items
Route::middleware(['auth'])->group(function () {
    Route::get('/api/inventory/search', [InventoryItemController::class, 'search'])->name('inventory.search');
});

// Products
Route::middleware(['auth'])->group(function () {
    Route::get('/branches/{branch}/products', [ProductController::class, 'index'])->name('products.branch.index');
    Route::post('/branches/{branch}/products', [ProductController::class, 'store'])->name('products.branch.store');
    Route::put('/branches/{branch}/products/{product}', [ProductController::class, 'update'])->name('products.branch.update');
    Route::delete('/branches/{branch}/products/{product}', [ProductController::class, 'destroy'])->name('products.branch.destroy');
});

// Public receipt route (no auth required)
Route::get('/sales/receipt/{reference}', [App\Http\Controllers\SaleController::class, 'publicReceipt'])->name('sales.public-receipt');

Route::get('/sales/verify-barcode', [SaleController::class, 'verifyBarcode'])->name('sales.verify-barcode');
Route::get('/sales/{sale}/print-receipt', [SaleController::class, 'printReceipt'])->name('sales.print-receipt');

// Test low stock notification
Route::post('/test-low-stock-notification', [App\Http\Controllers\SaleController::class, 'testLowStockNotification'])
    ->name('test.low.stock.notification');

require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
