<?php

use Illuminate\Support\Facades\Route;
use Webkul\InventoryPlus\Http\Controllers\Admin\BarcodeController;
use Webkul\InventoryPlus\Http\Controllers\Admin\ImportExportController;
use Webkul\InventoryPlus\Http\Controllers\Admin\InventoryMovementController;
use Webkul\InventoryPlus\Http\Controllers\Admin\StockAlertController;
use Webkul\InventoryPlus\Http\Controllers\Admin\TransferController;

Route::group([
    'prefix' => config('app.admin_url', 'admin') . '/inventory-plus',
    'middleware' => ['web', 'admin'],
    'as' => 'admin.inventory-plus.',
], function () {

    // Inventory Movements
    Route::controller(InventoryMovementController::class)->prefix('movements')->as('movements.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/product/{id}', 'productHistory')->name('product-history');
    });

    // Transfers
    Route::controller(TransferController::class)->prefix('transfers')->as('transfers.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'view')->name('view');
        Route::post('/{id}/ship', 'ship')->name('ship');
        Route::post('/{id}/receive', 'receive')->name('receive');
        Route::post('/{id}/cancel', 'cancel')->name('cancel');
    });

    // Barcode
    Route::controller(BarcodeController::class)->prefix('barcode')->as('barcode.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/lookup', 'lookup')->name('lookup');
        Route::post('/update-stock', 'updateStock')->name('update-stock');
        Route::post('/generate', 'generate')->name('generate');
        Route::post('/print-labels', 'printLabels')->name('print-labels');
        Route::post('/auto-generate', 'autoGenerate')->name('auto-generate');
    });

    // Stock Alerts
    Route::controller(StockAlertController::class)->prefix('alerts')->as('alerts.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::post('/check', 'check')->name('check');
    });

    // Import/Export
    Route::controller(ImportExportController::class)->prefix('import-export')->as('import-export.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/export', 'export')->name('export');
        Route::post('/import', 'import')->name('import');
        Route::get('/template', 'template')->name('template');
    });
});
