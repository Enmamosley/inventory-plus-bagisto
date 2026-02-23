<?php

namespace Webkul\InventoryPlus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentItem extends Model
{
    protected $table = 'stock_adjustment_items';

    protected $fillable = [
        'adjustment_id',
        'product_id',
        'qty_system',
        'qty_counted',
        'qty_difference',
        'notes',
    ];

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class, 'adjustment_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Product\Models\ProductProxy::modelClass(), 'product_id');
    }
}
