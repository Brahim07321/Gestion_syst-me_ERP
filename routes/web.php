<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockControllerController;
use App\Http\Controllers\FactureController;
use Illuminate\Support\Facades\Route;

// الصفحة الرئيسية = archive
Route::get('/', [FactureController::class, 'dashboard']);

// archive des factures
Route::get('/archife', [FactureController::class, 'index'])->name('factures.index');
// page création facture
Route::get('/facture', [ProductController::class, 'showInvoice'])->name('factures.create');

// save facture
Route::post('/facture', [FactureController::class, 'store'])->name('facture.store');
Route::get('/factures/{id}', [FactureController::class, 'show'])->name('factures.show'); 



Route::get('/Category', [CategoryController::class, 'ShowCategory']);
Route::post('/Category', [CategoryController::class, 'CreateCategory']);

Route::get('/product', [ProductController::class, 'index']);
Route::post('/product', [ProductController::class, 'createproduct']);

Route::get('/Customer', [CustomerController::class, 'ShowCustomers']);
Route::post('/Customer', [CustomerController::class, 'createCustomer']);
Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
Route::put('/Customer/{id}', [CustomerController::class, 'update'])->name('customers.update');



Route::get('/stock', [StockControllerController::class, 'indexstock'])->name('stock.index');
Route::get('/stock/{id}/edit', [StockControllerController::class, 'edit'])->name('stock.edit');
Route::put('/stock/{id}', [StockControllerController::class, 'update_stock'])->name('stock.update');
Route::get('/stock/export', [StockControllerController::class, 'export'])->name('stock.export');