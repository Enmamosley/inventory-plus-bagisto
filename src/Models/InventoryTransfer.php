<?php

namespace Webkul\InventoryPlus\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\InventoryPlus\Contracts\InventoryTransfer as InventoryTransferContract;
use Webkul\InventoryPlus\Database\Factories\InventoryTransferFactory;
use Webkul\InventoryPlus\Enums\TransferStatus;

class InventoryTransfer extends Model implements InventoryTransferContract
{
    use HasFactory;

    protected $table = 'inventory_transfers';

    protected $fillable = [
        'reference_number',
        'source_id',
        'destination_id',
        'user_id',
        'status',
        'notes',
        'shipped_at',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TransferStatus::class,
            'shipped_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Inventory\Models\InventorySourceProxy::modelClass(), 'source_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Inventory\Models\InventorySourceProxy::modelClass(), 'destination_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryTransferItem::class, 'transfer_id');
    }

    protected static function newFactory(): InventoryTransferFactory
    {
        return InventoryTransferFactory::new();
    }
}
