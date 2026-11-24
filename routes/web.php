<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

use App\Http\Controllers\LocaleController;
use App\Modules\Auth\Http\Controllers\AuthController;
use App\Modules\Auth\Http\Controllers\PasswordResetController;
use App\Modules\Catalog\Http\Controllers\CategoryController;
use App\Modules\Catalog\Http\Controllers\ProductController;
use App\Modules\Core\Http\Controllers\ActivityLogController;
use App\Modules\Core\Http\Controllers\DashboardController;
use App\Modules\CRM\Http\Controllers\ContactController;
use App\Modules\CRM\Http\Controllers\CustomerController;
use App\Modules\Expenses\Http\Controllers\ExpenseCategoryController;
use App\Modules\Expenses\Http\Controllers\ExpenseController;
use App\Modules\Inventory\Http\Controllers\StockMovementController;
use App\Modules\Inventory\Http\Controllers\WarehouseController;
use App\Modules\Reports\Http\Controllers\ReportController;
use App\Modules\Sales\Http\Controllers\InvoiceController;
use App\Modules\Sales\Http\Controllers\PaymentController;
use App\Modules\Sales\Http\Controllers\QuotationController;
use App\Modules\Settings\Http\Controllers\SettingsController;
use App\Modules\Suppliers\Http\Controllers\SupplierController;
use App\Modules\Users\Http\Controllers\RoleController;
use App\Modules\Users\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(Auth::check() ? 'dashboard' : 'login'))->name('home');

Route::get('/about', fn () => view('pages.about'))->name('about');
Route::get('/privacy', fn () => view('pages.privacy'))->name('privacy');
Route::get('/terms', fn () => view('pages.terms'))->name('terms');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');

    // One-click demo entry. The controller returns 404 when DEMO_MODE is off.
    Route::post('/demo/login', [AuthController::class, 'demoLogin'])->name('demo.login');

    Route::get('/forgot-password', [PasswordResetController::class, 'showRequestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1')->name('password.update');
});

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('customers', CustomerController::class);
    Route::get('customers/{customer}/contacts/create', [ContactController::class, 'create'])->name('contacts.create');
    Route::post('customers/{customer}/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::get('customers/{customer}/contacts/{contact}/edit', [ContactController::class, 'edit'])->name('contacts.edit');
    Route::put('customers/{customer}/contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('customers/{customer}/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');

    Route::resource('suppliers', SupplierController::class);

    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('warehouses', WarehouseController::class);

    Route::get('inventory', [StockMovementController::class, 'index'])->name('inventory.index');
    Route::get('inventory/create', [StockMovementController::class, 'create'])->name('inventory.create');
    Route::post('inventory', [StockMovementController::class, 'store'])->name('inventory.store');
    Route::get('inventory/low-stock', [StockMovementController::class, 'lowStock'])->name('inventory.low-stock');

    Route::resource('quotations', QuotationController::class);
    Route::patch('quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])->name('quotations.status');
    Route::post('quotations/{quotation}/convert', [QuotationController::class, 'convert'])->name('quotations.convert');
    Route::get('quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');

    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/issue', [InvoiceController::class, 'issue'])->name('invoices.issue');
    Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');

    Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store', 'show']);

    Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show']);
    Route::resource('expenses', ExpenseController::class);

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');

    Route::resource('users', UserController::class)->except(['show']);
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');

    Route::get('activity', [ActivityLogController::class, 'index'])->name('activity.index');

    Route::get('settings', [SettingsController::class, 'show'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::delete('settings/logo', [SettingsController::class, 'removeLogo'])->name('settings.logo.remove');
});
