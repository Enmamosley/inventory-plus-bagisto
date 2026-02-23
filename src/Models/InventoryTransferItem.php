<?php

namespace Webkul\InventoryPlus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransferItem extends Model
{
    protected $table = 'inventory_transfer_items';

    protected $fillable = [
        'transfer_id',
        'product_id',
        'qty_requested',
        'qty_shipped',
        'qty_received',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(InventoryTransfer::class, 'transfer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Product\Models\ProductProxy::modelClass(), 'product_id');
    }
}
