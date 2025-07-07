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
use App\Http\Controllers\UserController;
use App\Http\Middleware\BusinessAccess;
use App\Http\Middleware\CheckBranchAccess;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

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

    // User Management Routes (Admin Only)
    Route::group([], function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{user}/password', [UserController::class, 'updatePassword'])->name('users.updatePassword');
        Route::patch('/users/{user}/status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');
        Route::patch('/users/{user}/email-verification', [UserController::class, 'toggleEmailVerification'])->name('users.toggleEmailVerification');
        Route::post('/users/{user}/permissions', [UserController::class, 'assignPermissions'])->name('users.assignPermissions');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    });

    // Admin Business & Branch Management Routes (Admin Only)
    Route::group([], function () {
        Route::get('/admin/businesses', [BusinessController::class, 'adminIndex'])->name('admin.businesses.index');
        Route::get('/admin/businesses/{business}', [BusinessController::class, 'adminShow'])->name('admin.businesses.show');
        Route::get('/admin/businesses/{business}/edit', [BusinessController::class, 'edit'])->name('admin.businesses.edit');
        Route::post('/admin/businesses/{business}/documents/upload', [BusinessController::class, 'uploadDocument'])->name('admin.businesses.documents.upload');
        Route::delete('/admin/businesses/{business}/documents/{documentType}', [BusinessController::class, 'deleteDocument'])->name('admin.businesses.documents.delete');
        Route::get('/admin/branches', [BranchController::class, 'adminIndex'])->name('admin.branches.index');
        Route::get('/admin/branches/{branch}', [BranchController::class, 'adminShow'])->name('admin.branches.show');
    });

    // Main navigation routes
    Route::get('/branches', [BranchController::class, 'all'])->name('branches.all');
    Route::get('/sellers', [SellerController::class, 'all'])->name('sellers.all');
    Route::get('/sales', [SaleController::class, 'all'])->name('sales.all');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/discounts', [DiscountController::class, 'index'])->name('discounts.index');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::match(['get', 'post'], '/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/chat', function () {
        return Inertia::render('Chat', [
            'auth' => [
                'user' => auth()->user(),
            ],
        ]);
    })->name('chat');
    
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
                    Route::patch('/{branch}/toggle-status', [BranchController::class, 'toggleStatus'])->name('branches.toggleStatus');

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
    Route::get('businesses/{business}/branches/{branch}/download-barcode', [BranchController::class, 'downloadBarcode'])->name('branches.download-barcode');
    Route::get('businesses/{business}/branches/{branch}/print-barcode', [BranchController::class, 'printBarcode'])->name('branches.print-barcode');

    // Receipt History (Owner Only)
    Route::get('/sales/receipt-history', [SaleController::class, 'receiptHistory'])
        ->name('sales.receipt-history')
        ->middleware(\Spatie\Permission\Middlewares\RoleMiddleware::class.':owner');

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

    // Chat routes
    Route::middleware(['auth', 'verified', \App\Http\Middleware\RoleRouteAccess::class])->group(function () {
        Route::get('/chat/messages', [App\Http\Controllers\SellerChatController::class, 'generalHistory']);
        Route::get('/chat/messages/{sellerId}', [App\Http\Controllers\SellerChatController::class, 'getChatMessages']);
        Route::post('/chat/messages', [App\Http\Controllers\SellerChatController::class, 'generalSend']);
        
        // Advanced chat features
        Route::post('/chat/{chatId}/mark-read', [App\Http\Controllers\SellerChatController::class, 'markAsRead']);
        Route::post('/chat/{chatId}/typing', [App\Http\Controllers\SellerChatController::class, 'updateTypingStatus']);
        Route::post('/chat/online-status', [App\Http\Controllers\SellerChatController::class, 'updateOnlineStatus']);
        Route::put('/chat/messages/{messageId}/edit', [App\Http\Controllers\SellerChatController::class, 'editMessage']);
        Route::delete('/chat/messages/{messageId}', [App\Http\Controllers\SellerChatController::class, 'deleteMessage']);
        Route::get('/chat/{chatId}/online-status', [App\Http\Controllers\SellerChatController::class, 'getOnlineStatus']);
        Route::get('/chat/unread-count', [App\Http\Controllers\SellerChatController::class, 'getUnreadCount']);
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

Route::get('/settings/profile', function() {
    return redirect('/profile');
});

// Sellers routes
Route::get('/sellers/all', [SellerController::class, 'all'])->name('sellers.all');
Route::get('/businesses/{business}/branches/{branch}/sellers', [SellerController::class, 'index'])->name('sellers.index');

// Broadcasting authentication routes for Soketi
Route::post('/broadcasting/auth', function (Request $request) {
    return Broadcast::auth($request);
})->middleware(['auth']);

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('tax-groups', App\Http\Controllers\Admin\TaxGroupController::class);
});

// Password reset routes
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->middleware('guest')->name('password.request');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('guest')->name('password.email');
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->middleware('guest')->name('password.reset');
Route::post('/reset-password', [NewPasswordController::class, 'store'])->middleware('guest')->name('password.update');

Route::post('/reports/email', [\App\Http\Controllers\ReportController::class, 'email'])->middleware('auth');

require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
