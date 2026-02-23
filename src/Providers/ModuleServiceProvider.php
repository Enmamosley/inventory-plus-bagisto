<?php

namespace Webkul\InventoryPlus\Providers;

use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        \Webkul\InventoryPlus\Models\InventoryMovement::class,
        \Webkul\InventoryPlus\Models\InventoryTransfer::class,
        \Webkul\InventoryPlus\Models\StockAdjustment::class,
        \Webkul\InventoryPlus\Models\StockAlertRule::class,
    ];
}
