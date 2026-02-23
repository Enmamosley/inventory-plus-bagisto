<?php

namespace Webkul\InventoryPlus\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\InventoryPlus\Contracts\StockAdjustment as StockAdjustmentContract;
use Webkul\InventoryPlus\Database\Factories\StockAdjustmentFactory;

class StockAdjustment extends Model implements StockAdjustmentContract
{
    use HasFactory;

    protected $table = 'stock_adjustments';

    protected $fillable = [
        'reference_number',
        'inventory_source_id',
        'user_id',
        'type',
        'status',
        'reason',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function inventorySource(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Inventory\Models\InventorySourceProxy::modelClass(), 'inventory_source_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockAdjustmentItem::class, 'adjustment_id');
    }

    protected static function newFactory(): StockAdjustmentFactory
    {
        return StockAdjustmentFactory::new();
    }
}
