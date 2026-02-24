<?php

namespace Webkul\InventoryPlus\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Webkul\InventoryPlus\Services\BarcodeService;
use Webkul\InventoryPlus\Services\CsvImportExportService;
use Webkul\InventoryPlus\Services\InventoryMovementService;
use Webkul\InventoryPlus\Services\StockAlertService;
use Webkul\InventoryPlus\Services\TransferService;

class InventoryPlusServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/admin-routes.php');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'inventory-plus');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'inventory-plus');

        $this->app->register(EventServiceProvider::class);

        // Register artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Webkul\InventoryPlus\Console\Commands\CheckStockAlertsCommand::class,
            ]);
        }

        // Publish views and lang
        $this->publishes([
            __DIR__.'/../Resources/views' => resource_path('views/vendor/inventory-plus'),
        ], 'inventory-plus-views');

        $this->publishes([
            __DIR__.'/../Resources/lang' => lang_path('vendor/inventory-plus'),
        ], 'inventory-plus-lang');

        // Schedule stock alert checks
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('inventory-plus:check-alerts')->hourly();
        });
    }

    public function register(): void
    {
        $this->app->singleton(BarcodeService::class, fn () => new BarcodeService);
        $this->app->singleton(InventoryMovementService::class, fn () => new InventoryMovementService);
        $this->app->singleton(StockAlertService::class, fn () => new StockAlertService);

        $this->app->singleton(CsvImportExportService::class, fn ($app) => new CsvImportExportService(
            $app->make(InventoryMovementService::class)
        ));

        $this->app->singleton(TransferService::class, fn ($app) => new TransferService(
            $app->make(InventoryMovementService::class)
        ));

        $this->mergeConfigFrom(__DIR__.'/../Config/menu.php', 'menu.admin');
        $this->mergeConfigFrom(__DIR__.'/../Config/acl.php', 'acl');
    }
}
