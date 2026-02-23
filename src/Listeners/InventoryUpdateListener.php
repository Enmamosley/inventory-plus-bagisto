<?php

namespace Webkul\InventoryPlus\Listeners;

use Webkul\InventoryPlus\Enums\MovementType;
use Webkul\InventoryPlus\Services\InventoryMovementService;
use Webkul\InventoryPlus\Services\StockAlertService;

class InventoryUpdateListener
{
    public function __construct(
        protected InventoryMovementService $movementService,
        protected StockAlertService $alertService
    ) {}

    /**
     * Handle shipment created event — record sale movements.
     */
    public function handleShipmentCreated(mixed $shipment): void
    {
        if (! $shipment || ! $shipment->items) {
            return;
        }

        foreach ($shipment->items as $item) {
            $productId = $item->product_id ?? $item->order_item?->product_id;

            if (! $productId) {
                continue;
            }

            // Record movement (stock already deducted by Bagisto core)
            $this->movementService->record([
                'product_id' => $productId,
                'inventory_source_id' => $shipment->inventory_source_id,
                'order_id' => $shipment->order_id,
                'type' => MovementType::Sale,
                'qty_change' => -abs($item->qty),
                'reason' => "Shipment #{$shipment->id} for Order #{$shipment->order_id}",
                'reference_type' => 'shipment',
                'reference_id' => $shipment->id,
            ]);

            // Check alerts after stock change
            $this->alertService->checkProductAlerts($productId);
        }
    }

    /**
     * Handle order refund — record return movements.
     */
    public function handleRefundCreated(mixed $refund): void
    {
        if (! $refund || ! $refund->items) {
            return;
        }

        foreach ($refund->items as $item) {
            $productId = $item->product_id ?? $item->order_item?->product_id;

            if (! $productId || ! $item->qty) {
                continue;
            }

            // Find the first active inventory source for return
            $sourceId = \Illuminate\Support\Facades\DB::table('inventory_sources')
                ->where('status', 1)
                ->orderBy('priority')
                ->value('id');

            if (! $sourceId) {
                continue;
            }

            $this->movementService->record([
                'product_id' => $productId,
                'inventory_source_id' => $sourceId,
                'order_id' => $refund->order_id,
                'type' => MovementType::Return,
                'qty_change' => abs($item->qty),
                'reason' => "Refund #{$refund->id} for Order #{$refund->order_id}",
                'reference_type' => 'refund',
                'reference_id' => $refund->id,
            ]);

            $this->alertService->checkProductAlerts($productId);
        }
    }
}
