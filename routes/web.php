<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplyRequestController;
use App\Http\Controllers\IssuanceController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\BackupController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/track', [PublicController::class, 'track'])->name('public.track');
Route::get('/catalog', [PublicController::class, 'catalog'])->name('public.catalog');
Route::get('/checkout', [PublicController::class, 'checkout'])->name('public.checkout');
Route::post('/checkout', [PublicController::class, 'storeCheckout'])->name('public.checkout.store');
Route::post('/track/{id}/claim', [PublicController::class, 'claim'])->name('public.claim');
Route::post('/chatbot/ask', [ChatbotController::class, 'ask'])->name('chatbot.ask')->middleware('throttle:15,1');

/*
|--------------------------------------------------------------------------
| Authentication Routes (Guest)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login'])->name('login.post');
    Route::get('/forgot-password',  [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
    Route::get('/reset-password/{token}',  [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password',         [AuthController::class, 'resetPassword'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart-data');

    // Notifications
    Route::get('/notifications',           [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read',[NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/notifications/count',     [NotificationController::class, 'count'])->name('notifications.count');

    /*
    |--------------------------------------------------------------------------
    | Inventory & Categories
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin|supply-officer|auditor|budget-officer|accounting|regional-director|assistant-regional-director|supply-staff|budget-staff|accounting-staff|ard-staff|rd-staff'])->group(function () {
        Route::resource('inventory', InventoryController::class)->only(['index', 'show']);
        Route::get('/inventory/{id}/history', [InventoryController::class, 'history'])->name('inventory.history');
        Route::get('/inventory/{id}/barcode', [InventoryController::class, 'barcode'])->name('inventory.barcode');

        Route::resource('categories', CategoryController::class)->only(['index', 'show']);
        Route::get('/categories/data', [CategoryController::class, 'data'])->name('categories.data');
    });

    Route::middleware(['role:admin|supply-officer'])->group(function () {
        Route::resource('inventory', InventoryController::class)->except(['index', 'show']);
        Route::post('/inventory/{id}/adjust', [InventoryController::class, 'adjustStock'])->name('inventory.adjust');

        Route::resource('categories', CategoryController::class)->except(['index', 'show']);
    });

    /*
    |--------------------------------------------------------------------------
    | Suppliers
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin|supply-officer|auditor|supply-staff'])->group(function () {
        Route::resource('suppliers', SupplierController::class);
        Route::get('/suppliers/{id}/history', [SupplierController::class, 'purchaseHistory'])->name('suppliers.history');
    });

    /*
    |--------------------------------------------------------------------------
    | Supply Requests
    |--------------------------------------------------------------------------
    */
    Route::get('requests/checkout', [SupplyRequestController::class, 'checkout'])->name('requests.checkout');
    Route::resource('requests', SupplyRequestController::class);
    Route::middleware(['role:admin|supply-officer'])->group(function () {
        Route::post('/requests/{id}/approve', [SupplyRequestController::class, 'approve'])->name('requests.approve');
        Route::post('/requests/{id}/reject',  [SupplyRequestController::class, 'reject'])->name('requests.reject');
        Route::post('/requests/{id}/cancel',  [SupplyRequestController::class, 'cancel'])->name('requests.cancel');
        
        // This is still here if Supply Officer needs to issue it directly, but we will mostly rely on claim.
        Route::post('/requests/{id}/issue',   [SupplyRequestController::class, 'issue'])->name('requests.issue');
    });

    // Claim route for requesters
    Route::post('/requests/{id}/claim', [SupplyRequestController::class, 'claim'])->name('requests.claim');

    /*
    |--------------------------------------------------------------------------
    | Issuances
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin|supply-officer|auditor|supply-staff'])->group(function () {
        Route::resource('issuances', IssuanceController::class)->only(['index','show','destroy']);
        Route::get('/issuances/{id}/print', [IssuanceController::class, 'printSlip'])->name('issuances.print');
        Route::get('/issuances/{id}/pdf',   [IssuanceController::class, 'pdf'])->name('issuances.pdf');
    });

    /*
    |--------------------------------------------------------------------------
    | Returns
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin|supply-officer|supply-staff'])->group(function () {
        Route::resource('returns', ReturnController::class)->only(['index','create','store','show']);
    });

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin|supply-officer|auditor|supply-staff|budget-staff|accounting-staff|ard-staff|rd-staff'])->group(function () {
        Route::get('/reports',                    [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/inventory',          [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/low-stock',          [ReportController::class, 'lowStock'])->name('reports.low-stock');
        Route::get('/reports/issuance',           [ReportController::class, 'issuance'])->name('reports.issuance');
        Route::get('/reports/requests',           [ReportController::class, 'requests'])->name('reports.requests');
        Route::get('/reports/activity',           [ReportController::class, 'activity'])->name('reports.activity');
        Route::get('/reports/export/pdf/{type}',  [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    });

    /*
    |--------------------------------------------------------------------------
    | User & Role Management (Admin only)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class);
        Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

        Route::resource('roles', RoleController::class)->only(['index','store','destroy']);
        Route::resource('departments', DepartmentController::class);

        // Database Backup & Restore
        Route::get('/backups',               [BackupController::class, 'index'])->name('backups.index');
        Route::get('/backups/download-sql',  [BackupController::class, 'downloadSql'])->name('backups.download-sql');
        Route::get('/backups/download-json', [BackupController::class, 'downloadJson'])->name('backups.download-json');
        Route::post('/backups/restore',      [BackupController::class, 'restore'])->name('backups.restore');
    });

    // Profile
    Route::get('/profile',       [UserController::class, 'profile'])->name('profile');
    Route::put('/profile/update',[UserController::class, 'updateProfile'])->name('profile.update');

    /*
    |--------------------------------------------------------------------------
    | Procurement Module
    |--------------------------------------------------------------------------
    */
    Route::prefix('procurement')->name('procurement.')->group(function () {
        // Dashboard (all authenticated)
        Route::get('/dashboard', [\App\Http\Controllers\Procurement\ProcurementController::class, 'dashboard'])
            ->name('dashboard');


        // Purchase Orders (Admin + Supply Officer + Approvers + Officer Staff)
        Route::middleware(['role:admin|supply-officer|budget-officer|accounting|regional-director|assistant-regional-director|supply-staff|budget-staff|accounting-staff|ard-staff|rd-staff'])->group(function () {
            Route::resource('purchase-orders', \App\Http\Controllers\Procurement\PurchaseOrderController::class)
                ->names([
                    'index'   => 'purchase-orders.index',
                    'create'  => 'purchase-orders.create',
                    'store'   => 'purchase-orders.store',
                    'show'    => 'purchase-orders.show',
                    'edit'    => 'purchase-orders.edit',
                    'update'  => 'purchase-orders.update',
                    'destroy' => 'purchase-orders.destroy',
                ]);
            Route::post('/purchase-orders/{purchaseOrder}/send',
                [\App\Http\Controllers\Procurement\PurchaseOrderController::class, 'markSent'])
                ->name('purchase-orders.send');
            Route::post('/purchase-orders/{purchaseOrder}/cancel',
                [\App\Http\Controllers\Procurement\PurchaseOrderController::class, 'cancel'])
                ->name('purchase-orders.cancel');
            
            // Workflow Routes
            Route::post('/purchase-orders/{purchaseOrder}/receive',
                [\App\Http\Controllers\Procurement\PurchaseOrderController::class, 'receivePO'])
                ->name('purchase-orders.receive');
            Route::post('/purchase-orders/{purchaseOrder}/submit',
                [\App\Http\Controllers\Procurement\PurchaseOrderController::class, 'submitDraft'])
                ->name('purchase-orders.submit');
            Route::post('/purchase-orders/{purchaseOrder}/route-budget',
                [\App\Http\Controllers\Procurement\PurchaseOrderController::class, 'routeToBudget'])
                ->name('purchase-orders.route-budget');
            Route::post('/purchase-orders/{purchaseOrder}/route-forward',
                [\App\Http\Controllers\Procurement\PurchaseOrderController::class, 'routeForward'])
                ->name('purchase-orders.route-forward');
            Route::post('/purchase-orders/{purchaseOrder}/approve-budget',
                [\App\Http\Controllers\Procurement\PurchaseOrderController::class, 'approveBudget'])
                ->name('purchase-orders.approve-budget');
            Route::post('/purchase-orders/{purchaseOrder}/route-accounting',
                [\App\Http\Controllers\Procurement\PurchaseOrderController::class, 'routeToAccounting'])
                ->name('purchase-orders.route-accounting');
            Route::post('/purchase-orders/{purchaseOrder}/approve-accounting',
                [\App\Http\Controllers\Procurement\PurchaseOrderController::class, 'approveAccounting'])
                ->name('purchase-orders.approve-accounting');
            Route::post('/purchase-orders/{purchaseOrder}/route-rd',
                [\App\Http\Controllers\Procurement\PurchaseOrderController::class, 'routeToRD'])
                ->name('purchase-orders.route-rd');
            Route::post('/purchase-orders/{purchaseOrder}/approve-rd',
                [\App\Http\Controllers\Procurement\PurchaseOrderController::class, 'approveRD'])
                ->name('purchase-orders.approve-rd');
            Route::post('/purchase-orders/{purchaseOrder}/return',
                [\App\Http\Controllers\Procurement\PurchaseOrderController::class, 'returnPO'])
                ->name('purchase-orders.return');
            Route::get('/purchase-orders/{purchaseOrder}/print',
                [\App\Http\Controllers\Procurement\PurchaseOrderController::class, 'printPO'])
                ->name('purchase-orders.print');
            Route::get('/purchase-orders/{purchaseOrder}/pdf',
                [\App\Http\Controllers\Procurement\PurchaseOrderController::class, 'exportPdf'])
                ->name('purchase-orders.pdf');
        });



        // Deliveries / GRN
        Route::middleware(['role:admin|supply-officer|supply-staff'])->group(function () {
            Route::resource('deliveries', \App\Http\Controllers\Procurement\DeliveryController::class)
                ->only(['index','create','store','show'])
                ->names([
                    'index'  => 'deliveries.index',
                    'create' => 'deliveries.create',
                    'store'  => 'deliveries.store',
                    'show'   => 'deliveries.show',
                ]);
        });

        // Reports
        Route::middleware(['role:admin|supply-officer|auditor|supply-staff|budget-staff|accounting-staff|ard-staff|rd-staff'])->group(function () {
            Route::get('/reports', [\App\Http\Controllers\Procurement\ProcurementReportController::class, 'index'])
                ->name('reports.index');

            Route::get('/reports/purchase-orders',
                [\App\Http\Controllers\Procurement\ProcurementReportController::class, 'purchaseOrders'])
                ->name('reports.purchase-orders');
            Route::get('/reports/deliveries',
                [\App\Http\Controllers\Procurement\ProcurementReportController::class, 'deliveries'])
                ->name('reports.deliveries');
            Route::get('/reports/supplier-performance',
                [\App\Http\Controllers\Procurement\ProcurementReportController::class, 'supplierPerformance'])
                ->name('reports.supplier-performance');
            Route::get('/reports/export/pdf/{type}',
                [\App\Http\Controllers\Procurement\ProcurementReportController::class, 'exportPdf'])
                ->name('reports.export-pdf');
        });
    });
});
