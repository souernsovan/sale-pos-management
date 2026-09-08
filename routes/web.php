<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
})->name('health');

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('permission:view categories')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show']);
    });

    Route::middleware('permission:view suppliers')->group(function () {
        Route::resource('suppliers', SupplierController::class)->except(['show']);
    });

    Route::middleware('permission:create purchases')->group(function () {
        Route::get('purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    });
    Route::middleware('permission:view purchases')->group(function () {
        Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    });

    Route::middleware('permission:view products')->group(function () {
        Route::resource('products', ProductController::class);
        Route::get('products/{product}/barcode', [ProductController::class, 'barcode'])->name('products.barcode');
        Route::post('products/{product}/stock-movements', [StockMovementController::class, 'store'])->name('products.stock-movements.store');
    });

    Route::middleware('permission:access pos')->group(function () {
        Route::get('pos', function () {
            return view('pos.index');
        })->name('pos.index');
    });

    Route::middleware('permission:view sales')->group(function () {
        Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
        Route::get('sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
        Route::get('sales/{sale}/receipt/pdf', [SaleController::class, 'receiptPdf'])->name('sales.receipt.pdf');
    });
    Route::middleware('permission:void sales')->group(function () {
        Route::post('sales/{sale}/void', [SaleController::class, 'void'])->name('sales.void');
    });

    Route::middleware('permission:view customers')->group(function () {
        Route::resource('customers', CustomerController::class);
    });

    Route::middleware('permission:view reports')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/sales/export', [ReportController::class, 'exportSales'])->name('reports.sales.export');
        Route::get('reports/sales/pdf', [ReportController::class, 'exportSalesPdf'])->name('reports.sales.pdf');
        Route::get('reports/profit/export', [ReportController::class, 'exportProfit'])->name('reports.profit.export');
        Route::get('reports/profit/pdf', [ReportController::class, 'exportProfitPdf'])->name('reports.profit.pdf');
        Route::get('reports/best-sellers/export', [ReportController::class, 'exportBestSellers'])->name('reports.best-sellers.export');
        Route::get('reports/best-sellers/pdf', [ReportController::class, 'exportBestSellersPdf'])->name('reports.best-sellers.pdf');
    });

    Route::middleware('permission:manage settings')->group(function () {
        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    Route::middleware('permission:view users')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('permission:view roles')->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    });
    Route::middleware('permission:manage roles')->group(function () {
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::post('roles/{role}/reset-default', [RoleController::class, 'resetDefault'])->name('roles.reset-default');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    Route::middleware('permission:view audit log')->group(function () {
        Route::get('audit', [AuditController::class, 'index'])->name('audit.index');
    });
});

require __DIR__.'/auth.php';
