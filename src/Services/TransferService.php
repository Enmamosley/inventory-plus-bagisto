<?php

namespace Webkul\InventoryPlus\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Webkul\InventoryPlus\Enums\MovementType;
use Webkul\InventoryPlus\Enums\TransferStatus;
use Webkul\InventoryPlus\Models\InventoryTransfer;
use Webkul\InventoryPlus\Models\InventoryTransferItem;

class TransferService
{
    public function __construct(
        protected InventoryMovementService $movementService
    ) {}

    /**
     * Create a new inventory transfer request.
     *
     * @param  array{
     *     source_id: int,
     *     destination_id: int,
     *     notes?: string,
     *     items: array<array{product_id: int, qty: int}>
     * }  $data
     */
    public function create(array $data): InventoryTransfer
    {
        return DB::transaction(function () use ($data) {
            $transfer = InventoryTransfer::create([
                'reference_number' => $this->generateReferenceNumber(),
                'source_id' => $data['source_id'],
                'destination_id' => $data['destination_id'],
                'user_id' => Auth::id(),
                'status' => TransferStatus::Pending,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                InventoryTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'qty_requested' => $item['qty'],
                ]);
            }

            return $transfer->load('items');
        });
    }

    /**
     * Ship a transfer — deduct from source.
     */
    public function ship(InventoryTransfer $transfer): InventoryTransfer
    {
        if ($transfer->status !== TransferStatus::Pending) {
            throw new \RuntimeException('Transfer must be pending to ship.');
        }

        return DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                // Record outgoing movement from source
                $this->movementService->record([
                    'product_id' => $item->product_id,
                    'inventory_source_id' => $transfer->source_id,
                    'type' => MovementType::TransferOut,
                    'qty_change' => -$item->qty_requested,
                    'reason' => "Transfer #{$transfer->reference_number} to {$transfer->destination->name}",
                    'reference_type' => 'transfer',
                    'reference_id' => $transfer->id,
                ]);

                $item->update(['qty_shipped' => $item->qty_requested]);
            }

            $transfer->update([
                'status' => TransferStatus::InTransit,
                'shipped_at' => now(),
            ]);

            return $transfer->refresh();
        });
    }

    /**
     * Receive a transfer — add to destination.
     *
     * @param  array<int, int>|null  $receivedQtys  [item_id => qty_received], null = receive all
     */
    public function receive(InventoryTransfer $transfer, ?array $receivedQtys = null): InventoryTransfer
    {
        if ($transfer->status !== TransferStatus::InTransit) {
            throw new \RuntimeException('Transfer must be in transit to receive.');
        }

        return DB::transaction(function () use ($transfer, $receivedQtys) {
            foreach ($transfer->items as $item) {
                $qtyReceived = $receivedQtys[$item->id] ?? $item->qty_shipped;

                // Record incoming movement to destination
                $this->movementService->record([
                    'product_id' => $item->product_id,
                    'inventory_source_id' => $transfer->destination_id,
                    'type' => MovementType::TransferIn,
                    'qty_change' => $qtyReceived,
                    'reason' => "Transfer #{$transfer->reference_number} from {$transfer->source->name}",
                    'reference_type' => 'transfer',
                    'reference_id' => $transfer->id,
                ]);

                $item->update(['qty_received' => $qtyReceived]);
            }

            $transfer->update([
                'status' => TransferStatus::Completed,
                'received_at' => now(),
            ]);

            return $transfer->refresh();
        });
    }

    /**
     * Cancel a pending transfer.
     */
    public function cancel(InventoryTransfer $transfer): InventoryTransfer
    {
        if (! in_array($transfer->status, [TransferStatus::Pending, TransferStatus::InTransit])) {
            throw new \RuntimeException('Only pending or in-transit transfers can be cancelled.');
        }

        return DB::transaction(function () use ($transfer) {
            // If shipped, return stock to source
            if ($transfer->status === TransferStatus::InTransit) {
                foreach ($transfer->items as $item) {
                    if ($item->qty_shipped > 0) {
                        $this->movementService->record([
                            'product_id' => $item->product_id,
                            'inventory_source_id' => $transfer->source_id,
                            'type' => MovementType::Return,
                            'qty_change' => $item->qty_shipped,
                            'reason' => "Transfer #{$transfer->reference_number} cancelled — stock returned",
                            'reference_type' => 'transfer',
                            'reference_id' => $transfer->id,
                        ]);
                    }
                }
            }

            $transfer->update(['status' => TransferStatus::Cancelled]);

            return $transfer->refresh();
        });
    }

    private function generateReferenceNumber(): string
    {
        return 'TRF-' . strtoupper(date('Ymd')) . '-' . str_pad(
            (string) (InventoryTransfer::whereDate('created_at', today())->count() + 1),
            4,
            '0',
            STR_PAD_LEFT
        );
    }
}
