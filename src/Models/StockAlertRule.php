<?php

namespace Webkul\InventoryPlus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\InventoryPlus\Contracts\StockAlertRule as StockAlertRuleContract;

class StockAlertRule extends Model implements StockAlertRuleContract
{
    protected $table = 'stock_alert_rules';

    protected $fillable = [
        'name',
        'product_id',
        'inventory_source_id',
        'low_stock_threshold',
        'critical_stock_threshold',
        'notify_email',
        'email_recipients',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'notify_email' => 'boolean',
            'is_active' => 'boolean',
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
}
