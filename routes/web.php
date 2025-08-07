<?php
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
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
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ModifierController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\CashDrawerMovementController;
use App\Http\Controllers\TimeClockController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Auth\PosLoginController;

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
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Customer routes
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

    Route::post('/businesses/{business}/branches-with-store', [\App\Http\Controllers\BranchController::class, 'storeWithStore'])->name('branches.storeWithStore');

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

    Route::get('/purchases/location-stock', [\App\Http\Controllers\PurchaseController::class, 'getLocationStock'])->name('purchases.locationStock');
    Route::get('/purchases/supplier-items', [\App\Http\Controllers\PurchaseController::class, 'getSupplierItems'])->name('purchases.supplierItems');
    Route::get('/purchases/low-stock-items', [\App\Http\Controllers\PurchaseController::class, 'getLowStockItems'])->name('purchases.lowStockItems');
    Route::resource('purchases', PurchaseController::class);
    Route::post('/purchases/{purchase}/approve', [\App\Http\Controllers\PurchaseController::class, 'approve'])->name('purchases.approve');
    Route::resource('purchase-items', PurchaseItemController::class);
    Route::resource('items', ItemController::class);
    Route::resource('categories', CategoryController::class);
    Route::post('/categories', [\App\Http\Controllers\CategoryController::class, 'store']);
    Route::resource('modifiers', ModifierController::class);
    Route::resource('discounts', DiscountController::class);
    Route::post('/tax-groups', [\App\Http\Controllers\TaxGroupController::class, 'store'])->name('tax-groups.store');
    Route::post('/purchases/{purchase}/send-email', [\App\Http\Controllers\PurchaseController::class, 'sendEmail'])->name('purchases.sendEmail');
    Route::get('/purchases/{purchase}/receive', [\App\Http\Controllers\PurchaseController::class, 'showReceiveForm'])->name('purchases.receiveForm');
    Route::post('/purchases/{purchase}/receive', [\App\Http\Controllers\PurchaseController::class, 'receive'])->name('purchases.receive');
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

Route::get('/test-mpesa', [\App\Http\Controllers\PaymentTestController::class, 'testMpesa']);

Route::get('/test', function () {
    return inertia('FlutterwaveTest');
})->middleware(['auth']); // Add 'admin' if you have it

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
    Route::post('/tax-groups', [\App\Http\Controllers\Admin\TaxGroupController::class, 'store']);
});

// Password reset routes
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->middleware('guest')->name('password.request');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('guest')->name('password.email');
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->middleware('guest')->name('password.reset');
Route::post('/reset-password', [NewPasswordController::class, 'store'])->middleware('guest')->name('password.update');

Route::post('/reports/email', [\App\Http\Controllers\ReportController::class, 'email'])->middleware('auth');

require __DIR__.'/auth.php';
require __DIR__.'/settings.php';

Route::middleware(['auth', 'role:owner'])->group(function () {
    Route::resource('stores', StoreController::class);
    Route::resource('stock-transfers', StockTransferController::class);
    Route::post('stock-transfers/{stockTransfer}/receive', [StockTransferController::class, 'receive'])->name('stock-transfers.receive');
    Route::resource('suppliers', SupplierController::class);
    Route::post('/employers/roles', [\App\Http\Controllers\EmployerController::class, 'storeRole'])->name('employers.roles.store');
    Route::get('/employers/roles/{role}/edit', [\App\Http\Controllers\EmployerController::class, 'editRole'])->name('employers.roles.edit');
    Route::post('/employers/roles/{role}/update', [\App\Http\Controllers\EmployerController::class, 'updateRole'])->name('employers.roles.update');
});

// Supplier routes
Route::middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
});

// Public or shared supplier routes
Route::middleware(['auth'])->group(function () {
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
});

Route::get('/api/items/search', [\App\Http\Controllers\ItemController::class, 'search'])->name('items.search');

Route::middleware(['auth', 'verified', \App\Http\Middleware\RoleRouteAccess::class])->group(function () {
    Route::get('/employers', [\App\Http\Controllers\EmployerController::class, 'index'])->name('employers.index');
    Route::get('/employers/create', [\App\Http\Controllers\EmployerController::class, 'create'])->name('employers.create');
    Route::post('/employers', [\App\Http\Controllers\EmployerController::class, 'store'])->name('employers.store');
    Route::get('/employers/access-control', [\App\Http\Controllers\EmployerController::class, 'accessControl'])->name('employers.accessControl');
    Route::get('/employers/roles/create', [\App\Http\Controllers\EmployerController::class, 'createRole'])->name('employers.roles.create');
});

// POS routes - handle authentication redirects
Route::get('/pos', function () {
    // If user is authenticated and has POS access, redirect to dashboard
    if (Auth::check() && session('pos_login')) {
        return redirect('/pos/dashboard');
    }
    // Otherwise show login page
    return Inertia::render('POS/Login');
})->name('pos.login');

// POS login routes (for unauthenticated users)
Route::post('/pos/login', [PosLoginController::class, 'login']);
Route::post('/pos/logout', [PosLoginController::class, 'logout'])->name('pos.logout');

// POS area (requires POS login)
Route::prefix('pos')->middleware(['auth', 'pos.only'])->group(function () {
    Route::post('/ticket/{ticket}/cancel', [POSController::class, 'cancelTicket']);
    Route::get('/dashboard', [\App\Http\Controllers\POSController::class, 'index'])->name('pos.index');
    Route::post('/verify-pin', [\App\Http\Controllers\POSController::class, 'verifyPin']);
    Route::post('/ticket/store', [\App\Http\Controllers\POSController::class, 'storeTicket'])->name('pos.ticket.store');
    Route::get('/ticket/{id}', [\App\Http\Controllers\POSController::class, 'getTicket'])->name('pos.ticket.get');
    Route::post('/ticket/{id}/update-payment', [\App\Http\Controllers\POSController::class, 'updateTicketPayment'])->name('pos.ticket.update-payment');
    Route::post('/ticket/{id}/update', [\App\Http\Controllers\POSController::class, 'updateTicket'])->name('pos.ticket.update');
    Route::get('/tickets/active', [\App\Http\Controllers\POSController::class, 'listActiveTickets'])->name('pos.tickets.active');
    Route::post('/ticket/{id}/convert-to-sale', [\App\Http\Controllers\POSController::class, 'convertTicketToSale'])->name('pos.ticket.convert-to-sale');

// POS M-PESA Routes
Route::post('/mpesa/initiate', [\App\Http\Controllers\POSMpesaController::class, 'initiateStkPush'])->name('pos.mpesa.initiate');
Route::post('/mpesa/check-status', [\App\Http\Controllers\POSMpesaController::class, 'checkPaymentStatus'])->name('pos.mpesa.check-status');
Route::get('/mpesa/credentials', [\App\Http\Controllers\POSMpesaController::class, 'getCredentials'])->name('pos.mpesa.credentials');
Route::get('/mpesa/test-session', [\App\Http\Controllers\POSMpesaController::class, 'testSession'])->name('pos.mpesa.test-session');
});

Route::middleware(['auth', 'verified', \App\Http\Middleware\RoleRouteAccess::class])->group(function () {
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::post('/devices', [DeviceController::class, 'store'])->name('devices.store');
    Route::delete('/devices/{device}', [DeviceController::class, 'destroy'])->name('devices.destroy');
});

Route::get('/api/check-device', function (\Illuminate\Http\Request $request) {
    $uuid = $request->query('uuid');
    $exists = \App\Models\PosDevice::where('device_uuid', $uuid)
        ->where('is_disabled', false)
        ->exists();
    return response()->json(['deviceRegistered' => $exists]);
});

Route::get('/api/pos-device-status', function (\Illuminate\Http\Request $request) {
    $uuid = $request->query('uuid');
    $device = \App\Models\PosDevice::where('device_uuid', $uuid)->first();
    return response()->json([
        'is_disabled' => $device ? (bool)$device->is_disabled : null,
    ]);
});

Route::post('/units', [UnitController::class, 'store']);

Route::middleware(['auth'])->group(function () {
    Route::post('/shifts/open', [ShiftController::class, 'open']);
    Route::post('/shifts/close', [ShiftController::class, 'close']);
    Route::post('/cash-drawer-movements', [CashDrawerMovementController::class, 'store']);
    Route::post('/time-clock/clock-in', [TimeClockController::class, 'clockIn']);
    Route::post('/time-clock/clock-out', [TimeClockController::class, 'clockOut']);
});

Route::get('/pos/purchase/{id}', function ($id) {
    return Inertia::render('POS/Purchase', ['id' => $id]);
})->middleware(['auth', 'verified']);

// PesaPal Payment Routes - Commented out until controller is created
// Route::prefix('pesapal')->group(function () {
//     Route::post('/initiate-payment', [\App\Http\Controllers\PesaPalController::class, 'initiatePayment'])->name('pesapal.initiate');
//     Route::post('/check-status', [\App\Http\Controllers\PesaPalController::class, 'checkStatus'])->name('pesapal.check-status');
//     Route::post('/callback', [\App\Http\Controllers\PesaPalController::class, 'callback'])->name('pesapal.callback');
//     Route::get('/test', [\App\Http\Controllers\PesaPalController::class, 'test'])->name('pesapal.test');
    
//     // Simple test route for CSRF debugging
//     Route::post('/test-csrf', function() {
//         return response()->json(['message' => 'CSRF test successful', 'timestamp' => now()]);
//     });
// });

// Backoffice area (requires password login)
Route::middleware(['auth', 'backoffice.only'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);
    // ...other backoffice routes
});
