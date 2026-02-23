<?php

namespace Webkul\InventoryPlus\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Webkul\InventoryPlus\Enums\MovementType;
use Webkul\InventoryPlus\Models\InventoryMovement;
use Webkul\Product\Models\ProductInventory;

class InventoryMovementService
{
    /**
     * Record an inventory movement and update product_inventories.
     *
     * @param  array{
     *     product_id: int,
     *     inventory_source_id: int,
     *     type: MovementType|string,
     *     qty_change: int,
     *     reason?: string,
     *     order_id?: int,
     *     reference_type?: string,
     *     reference_id?: int,
     *     user_id?: int,
     *     metadata?: array
     * }  $data
     */
    public function record(array $data): InventoryMovement
    {
        return DB::transaction(function () use ($data) {
            $productId = $data['product_id'];
            $sourceId = $data['inventory_source_id'];

            // Get current qty
            $inventory = ProductInventory::query()
                ->where('product_id', $productId)
                ->where('inventory_source_id', $sourceId)
                ->first();

            $qtyBefore = $inventory?->qty ?? 0;
            $qtyChange = (int) $data['qty_change'];
            $qtyAfter = $qtyBefore + $qtyChange;

            // Ensure non-negative (unless it's a sale that brings below zero for backorders)
            $type = $data['type'] instanceof MovementType
                ? $data['type']
                : MovementType::from($data['type']);

            // Create movement record
            $movement = InventoryMovement::create([
                'product_id' => $productId,
                'inventory_source_id' => $sourceId,
                'order_id' => $data['order_id'] ?? null,
                'user_id' => $data['user_id'] ?? Auth::id(),
                'type' => $type,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'qty_before' => $qtyBefore,
                'qty_change' => $qtyChange,
                'qty_after' => $qtyAfter,
                'reason' => $data['reason'] ?? null,
                'metadata' => $data['metadata'] ?? null,
            ]);

            // Update actual inventory
            if ($inventory) {
                $inventory->update(['qty' => $qtyAfter]);
            } else {
                ProductInventory::create([
                    'product_id' => $productId,
                    'inventory_source_id' => $sourceId,
                    'qty' => $qtyAfter,
                    'vendor_id' => 0,
                ]);
            }

            return $movement;
        });
    }

    /**
     * Record a batch of movements (e.g., from CSV import).
     *
     * @param  array<array>  $items
     * @return array<InventoryMovement>
     */
    public function recordBatch(array $items): array
    {
        $movements = [];

        foreach ($items as $item) {
            $movements[] = $this->record($item);
        }

        return $movements;
    }

    /**
     * Get movement history for a product.
     */
    public function getProductHistory(int $productId, ?int $sourceId = null, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        $query = InventoryMovement::query()
            ->where('product_id', $productId)
            ->orderByDesc('created_at');

        if ($sourceId) {
            $query->where('inventory_source_id', $sourceId);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get total qty across all sources for a product.
     */
    public function getTotalStock(int $productId): int
    {
        return (int) ProductInventory::query()
            ->where('product_id', $productId)
            ->sum('qty');
    }
}
