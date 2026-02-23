<?php

namespace Webkul\InventoryPlus\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [];

    /**
     * Boot event listeners.
     * Uses Bagisto-style event names.
     */
    public function boot(): void
    {
        parent::boot();

        // Listen to shipment creation for sale movement logging
        \Illuminate\Support\Facades\Event::listen(
            'sales.shipment.save.after',
            [\Webkul\InventoryPlus\Listeners\InventoryUpdateListener::class, 'handleShipmentCreated']
        );

        // Listen to refund creation for return movement logging
        \Illuminate\Support\Facades\Event::listen(
            'sales.refund.save.after',
            [\Webkul\InventoryPlus\Listeners\InventoryUpdateListener::class, 'handleRefundCreated']
        );
    }
}
