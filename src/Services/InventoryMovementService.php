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
     * Record a stock adjustment atomically (scan & update workflow).
     *
     * All qty reads and writes happen inside a single transaction with
     * a row-level lock, preventing race conditions.
     *
     * @param  array{
     *     product_id: int,
     *     inventory_source_id: int,
     *     action: string,
     *     qty: int,
     *     reason?: string,
     *     user_id?: int,
     * }  $data
     * @return array{success: bool, message: string, new_qty?: int, movement?: InventoryMovement}
     */
    public function recordAdjustment(array $data): array
    {
        $action = $data['action'];   // set | add | subtract
        $qty = (int) $data['qty'];

        return DB::transaction(function () use ($data, $action, $qty) {
            $productId = $data['product_id'];
            $sourceId = $data['inventory_source_id'];

            // Lock the row to prevent concurrent modifications
            $inventory = ProductInventory::query()
                ->where('product_id', $productId)
                ->where('inventory_source_id', $sourceId)
                ->lockForUpdate()
                ->first();

            $currentQty = $inventory?->qty ?? 0;

            $qtyChange = match ($action) {
                'set' => $qty - $currentQty,
                'add' => $qty,
                'subtract' => -$qty,
                default => 0,
            };

            if ($qtyChange === 0) {
                return [
                    'success' => true,
                    'message' => 'no-change',
                    'new_qty' => $currentQty,
                ];
            }

            $newQty = $currentQty + $qtyChange;

            if ($newQty < 0) {
                return [
                    'success' => false,
                    'message' => 'negative-stock',
                ];
            }

            $movement = InventoryMovement::create([
                'product_id' => $productId,
                'inventory_source_id' => $sourceId,
                'user_id' => $data['user_id'] ?? Auth::id(),
                'type' => MovementType::Adjustment,
                'reference_type' => null,
                'reference_id' => null,
                'order_id' => null,
                'qty_before' => $currentQty,
                'qty_change' => $qtyChange,
                'qty_after' => $newQty,
                'reason' => $data['reason'] ?? null,
                'metadata' => [
                    'action' => $action,
                    'input_qty' => $qty,
                    'source' => 'barcode_scanner',
                ],
            ]);

            if ($inventory) {
                $inventory->update(['qty' => $newQty]);
            } else {
                ProductInventory::create([
                    'product_id' => $productId,
                    'inventory_source_id' => $sourceId,
                    'qty' => $newQty,
                    'vendor_id' => 0,
                ]);
            }

            return [
                'success' => true,
                'message' => 'updated',
                'new_qty' => $newQty,
                'movement' => $movement,
            ];
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
