<?php

namespace Webkul\InventoryPlus\Console\Commands;

use Illuminate\Console\Command;
use Webkul\InventoryPlus\Services\StockAlertService;

class CheckStockAlertsCommand extends Command
{
    protected $signature = 'inventory-plus:check-alerts';

    protected $description = 'Check stock levels against alert rules and send notifications';

    public function handle(StockAlertService $alertService): int
    {
        $this->info('Checking stock alerts...');

        $count = $alertService->checkAlerts();

        $this->info("Done. {$count} alert(s) triggered.");

        return self::SUCCESS;
    }
}
