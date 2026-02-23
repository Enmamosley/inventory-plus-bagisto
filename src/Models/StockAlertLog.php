<?php

namespace Webkul\InventoryPlus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\InventoryPlus\Enums\AlertType;

class StockAlertLog extends Model
{
    protected $table = 'stock_alert_logs';

    protected $fillable = [
        'rule_id',
        'product_id',
        'inventory_source_id',
        'alert_type',
        'current_qty',
        'threshold',
        'notified',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'alert_type' => AlertType::class,
            'notified' => 'boolean',
            'notified_at' => 'datetime',
        ];
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(StockAlertRule::class, 'rule_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Product\Models\ProductProxy::modelClass(), 'product_id');
    }
}
