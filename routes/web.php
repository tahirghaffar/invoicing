<?php

use App\Http\Controllers\Admin\BusinessController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\BusinessSandboxScenarioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessSelectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BusinessProfileController;
use App\Http\Controllers\FbrSettingsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\FbrReferenceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SystemMaintenanceController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    if (!auth()->check()) {
        return redirect()->route('login');
    }

    if (auth()->user()->hasSystemRole('super-admin')) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('dashboard');

});

Route::get('/system-maintenance/migrate', [SystemMaintenanceController::class, 'migrate']);

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login.submit');

});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');


    /*
    |--------------------------------------------------------------------------
    | Super Admin
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('system.admin')
        ->group(function () {

            Route::get('/dashboard', [AdminDashboardController::class, 'index'])
                ->name('dashboard');

            Route::get('/businesses', [BusinessController::class, 'index'])
                ->name('businesses.index');

            Route::get('/businesses/create', [BusinessController::class, 'create'])
                ->name('businesses.create');

            Route::post('/businesses', [BusinessController::class, 'store'])
                ->name('businesses.store');

            Route::get(
                '/businesses/{business}/sandbox-scenarios',
                [BusinessSandboxScenarioController::class, 'edit']
            )->name('businesses.sandbox-scenarios.edit');


            Route::put(
                '/businesses/{business}/sandbox-scenarios',
                [BusinessSandboxScenarioController::class, 'update']
            )->name('businesses.sandbox-scenarios.update');

        });


    /*
    |--------------------------------------------------------------------------
    | Business Selection
    |--------------------------------------------------------------------------
    */

    Route::get('/select-business', [BusinessSelectionController::class, 'index'])
        ->name('business.select');

    Route::post('/select-business', [BusinessSelectionController::class, 'store'])
        ->name('business.select.store');


    /*
    |--------------------------------------------------------------------------
    | Business Application
    |--------------------------------------------------------------------------
    */

    Route::middleware('current.business')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get(
            '/settings/business-profile',
            [BusinessProfileController::class, 'edit']
        )->name('business.profile.edit');

        Route::put(
            '/settings/business-profile',
            [BusinessProfileController::class, 'update']
        )->name('business.profile.update');

        Route::get(
            '/settings/fbr',
            [FbrSettingsController::class, 'edit']
        )->name('fbr.settings.edit');

        Route::put(
            '/settings/fbr',
            [FbrSettingsController::class, 'update']
        )->name('fbr.settings.update');

        Route::get(
            '/customers/search',
            [CustomerController::class, 'search']
        )->name('customers.search');


        Route::get(
            '/customers',
            [CustomerController::class, 'index']
        )->name('customers.index');


        Route::get(
            '/customers/create',
            [CustomerController::class, 'create']
        )->name('customers.create');


        Route::post(
            '/customers',
            [CustomerController::class, 'store']
        )->name('customers.store');


        Route::get(
            '/customers/{customer}/edit',
            [CustomerController::class, 'edit']
        )->name('customers.edit');


        Route::put(
            '/customers/{customer}',
            [CustomerController::class, 'update']
        )->name('customers.update');


        Route::delete(
            '/customers/{customer}',
            [CustomerController::class, 'destroy']
        )->name('customers.destroy');


        Route::get(
            '/products/search',
            [ProductController::class, 'search']
        )->name('products.search');


        Route::get(
            '/products',
            [ProductController::class, 'index']
        )->name('products.index');


        Route::get(
            '/products/create',
            [ProductController::class, 'create']
        )->name('products.create');


        Route::post(
            '/products',
            [ProductController::class, 'store']
        )->name('products.store');


        Route::get(
            '/products/{product}/edit',
            [ProductController::class, 'edit']
        )->name('products.edit');


        Route::put(
            '/products/{product}',
            [ProductController::class, 'update']
        )->name('products.update');


        Route::delete(
            '/products/{product}',
            [ProductController::class, 'destroy']
        )->name('products.destroy');

        Route::post(
            '/fbr/references/sync',
            [FbrReferenceController::class, 'sync']
        )->name('fbr.references.sync');


        Route::get(
            '/fbr/references/provinces',
            [FbrReferenceController::class, 'provinces']
        )->name('fbr.references.provinces');


        Route::get(
            '/fbr/references/transaction-types',
            [FbrReferenceController::class, 'transactionTypes']
        )->name('fbr.references.transaction-types');


        Route::get(
            '/fbr/references/uoms',
            [FbrReferenceController::class, 'uoms']
        )->name('fbr.references.uoms');


        Route::get(
            '/fbr/references/hs-codes',
            [FbrReferenceController::class, 'hsCodes']
        )->name('fbr.references.hs-codes');


        Route::get(
            '/fbr/references/rates',
            [FbrReferenceController::class, 'rates']
        )->name('fbr.references.rates');

        Route::get(
            '/invoices',
            [InvoiceController::class, 'index']
        )->name('invoices.index');


        Route::get(
            '/invoices/create',
            [InvoiceController::class, 'create']
        )->name('invoices.create');


        Route::post(
            '/invoices/save',
            [InvoiceController::class, 'save']
        )->name('invoices.save');


        Route::get(
            '/invoices/{invoice}/edit',
            [InvoiceController::class, 'edit']
        )->name('invoices.edit');

        Route::get(
            '/invoices/{invoice}/preview',
            [InvoiceController::class, 'preview']
        )->name('invoices.preview');

        Route::get(
            '/invoices/{invoice}/print',
            [InvoiceController::class, 'printView']
        )->name('invoices.print');

        Route::get(
            '/invoices/{invoice}/pdf',
            [InvoiceController::class, 'pdf']
        )->name('invoices.pdf');

        Route::get(
            '/invoices/{invoice}/fbr-json',
            [InvoiceController::class, 'fbrJson']
        )->name('invoices.fbr-json');

        Route::post(
            '/invoices/{invoice}/validate-sandbox',
            [InvoiceController::class, 'validateSandbox']
        )->name('invoices.validate-sandbox');

        Route::post(
            '/invoices/{invoice}/post-sandbox',
            [InvoiceController::class, 'postSandbox']
        )->name('invoices.post-sandbox');

        Route::get(
            '/invoices/{invoice}/submissions',
            [InvoiceController::class, 'submissions']
        )->name('invoices.submissions');

        Route::post(
            '/invoices/{invoice}/submit-production',
            [InvoiceController::class, 'submitProduction']
        )->name('invoices.submit-production');

        Route::post(
            '/invoices/{invoice}/validate-fbr',
            [InvoiceController::class,'validateFbr']
        )->name('invoices.validate-fbr');

    });

});
