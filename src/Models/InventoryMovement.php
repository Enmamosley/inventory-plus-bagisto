<?php

namespace Webkul\InventoryPlus\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\InventoryPlus\Contracts\InventoryMovement as InventoryMovementContract;
use Webkul\InventoryPlus\Database\Factories\InventoryMovementFactory;
use Webkul\InventoryPlus\Enums\MovementType;

class InventoryMovement extends Model implements InventoryMovementContract
{
    use HasFactory;

    protected $table = 'inventory_movements';

    protected $fillable = [
        'product_id',
        'inventory_source_id',
        'order_id',
        'user_id',
        'type',
        'reference_type',
        'reference_id',
        'qty_before',
        'qty_change',
        'qty_after',
        'reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => MovementType::class,
            'metadata' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Product\Models\ProductProxy::modelClass(), 'product_id');
    }

    public function inventorySource(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Inventory\Models\InventorySourceProxy::modelClass(), 'inventory_source_id');
    }

    protected static function newFactory(): InventoryMovementFactory
    {
        return InventoryMovementFactory::new();
    }
}
