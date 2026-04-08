<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockControllerController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\PurchaseImportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanySettingController;




// الصفحة الرئيسية
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard
Route::get('/dashboard', [FactureController::class, 'dashboard'])
    ->middleware('auth')
    ->name('dashboard');



// جميع routes محمية
Route::middleware('auth')->group(function () {






Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
});

    // =========================
    // FACTURES
    // =========================
    Route::get('/archife', [FactureController::class, 'index'])->name('factures.index');
    Route::get('/facture', [ProductController::class, 'showInvoice'])->name('factures.create');
    Route::post('/facture', [FactureController::class, 'store'])->name('facture.store');
    Route::get('/factures/{id}', [FactureController::class, 'show'])->name('factures.show');
    Route::delete('/factures/{id}', [FactureController::class, 'destroy'])
    ->middleware(['auth', 'admin'])
    ->name('factures.destroy');

    Route::post('/factures/{id}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::post('/factures/cancel/{id}', [FactureController::class, 'cancel'])->middleware(['auth', 'admin'])->name('factures.cancel');

    Route::get('/factures/export/excel', [FactureController::class, 'exportExcel'])->name('factures.export.excel');
    Route::get('/factures/export/pdf', [FactureController::class, 'exportPdf'])->name('factures.export.pdf');


    Route::get('/factures/{id}/edit', [FactureController::class, 'edit'])->name('factures.edit');
Route::put('/factures/{id}', [FactureController::class, 'update'])->name('factures.update');


Route::put('/payments/{id}', [PaymentController::class, 'update'])->name('payments.update');
Route::delete('/payments/{id}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    // =========================
    // CATEGORIES
    // =========================
    Route::get('/Category', [CategoryController::class, 'ShowCategory'])->name('category.index');
    Route::post('/Category', [CategoryController::class, 'CreateCategory'])->middleware(['auth', 'admin'])->name('category.store');
    Route::delete('/Category/{id}', [CategoryController::class, 'destroy'])->middleware(['auth', 'admin'])->name('Category.destroy');

    // =========================
    // PRODUCTS
    // =========================
    Route::get('/product', [ProductController::class, 'index'])->name('product.index');
    Route::post('/product', [ProductController::class, 'createproduct'])->name('product.store');
    Route::put('/product/{id}', [ProductController::class, 'update'])->name('product.update');
    Route::delete('/product/{id}', [ProductController::class, 'destroy'])->middleware(['auth', 'admin'])->name('product.destroy');

    Route::post('/import-products', [ProductController::class, 'import'])->name('products.import');
    Route::get('/template-products', [ProductController::class, 'downloadTemplate'])->name('products.template');

    // =========================
    // CUSTOMERS
    // =========================
    Route::get('/Customer', [CustomerController::class, 'ShowCustomers'])->name('customers.index');
    Route::post('/Customer', [CustomerController::class, 'createCustomer'])->name('customers.store');
    Route::put('/Customer/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/Customer/{id}', [CustomerController::class, 'destroy'])->middleware(['auth', 'admin'])->name('customers.destroy');

    Route::get('/customers/export/pdf', [CustomerController::class, 'exportPdf'])->name('customers.export.pdf');
    Route::get('/customers/export/excel', [CustomerController::class, 'exportExcel'])->name('customers.export.excel');

    // =========================
    // STOCK
    // =========================
    Route::get('/stock', [StockControllerController::class, 'indexstock'])->name('stock.index');
    Route::put('/stock/{id}', [StockControllerController::class, 'update_stock'])->name('stock.update');
    Route::get('/stock/export', [StockControllerController::class, 'export'])->name('stock.export');

    Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('stock.movements');
    Route::get('/stock-movements/export/excel', [StockMovementController::class, 'exportExcel'])->name('stock.movements.export.excel');
    Route::get('/stock-movements/export/pdf', [StockMovementController::class, 'exportPdf'])->name('stock.movements.export.pdf');

    // =========================
    // PURCHASES
    // =========================
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchase/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchase', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/purchases/{id}', [PurchaseController::class, 'show'])->name('purchases.show');

    Route::post('/purchases/cancel/{id}', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
    Route::post('/purchases/status/{id}', [PurchaseController::class, 'markAsReceived'])->name('purchases.status');
    //Route::delete('/purchases/{id}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');

    Route::delete('/purchases/{id}', [PurchaseController::class, 'destroy'])->middleware(['auth', 'admin'])->name('purchases.destroy');

    Route::get('/purchases/export/excel', [PurchaseController::class, 'exportExcel'])->name('purchases.export.excel');
    Route::get('/purchases/export/pdf', [PurchaseController::class, 'exportPdf'])->name('purchases.export.pdf');

    Route::post('/purchases/import-preview', [PurchaseImportController::class, 'preview'])->name('purchases.import.preview');
    Route::post('/purchases/import-confirm', [PurchaseImportController::class, 'confirm'])->name('purchases.import.confirm');

    // =========================
    // SUPPLIERS
    // =========================
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy'])->middleware(['auth', 'admin'])->name('suppliers.destroy');
    Route::get('/suppliers/export', [SupplierController::class, 'exportExcel'])->name('suppliers.export');
    Route::post('/suppliers/import', [SupplierController::class, 'import'])->name('suppliers.import');
    Route::get('/suppliers/template', [SupplierController::class, 'downloadTemplate'])->name('suppliers.template');

    // =========================
    // EXPENSES
    // =========================
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy'])->middleware(['auth', 'admin'])->name('expenses.destroy');
    // =========================
    // setting
    // =========================

    Route::get('/settings/company', [CompanySettingController::class, 'edit'])->name('settings.company.edit');
    Route::post('/settings/company', [CompanySettingController::class, 'update'])->name('settings.company.update');
    // =========================
    // REPORTS
    // =========================
    Route::get('/reports', [FactureController::class, 'report'])->name('reports');

    Route::get('/profile', function () {
        return redirect()->route('settings.company.edit');
    })->name('profile.edit');
});

// auth routes
require __DIR__.'/auth.php';

