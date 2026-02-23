<?php

namespace Webkul\InventoryPlus\Enums;

enum AlertType: string
{
    case LowStock = 'low_stock';
    case CriticalStock = 'critical_stock';
    case OutOfStock = 'out_of_stock';
    case BackInStock = 'back_in_stock';

    public function label(): string
    {
        return match ($this) {
            self::LowStock => 'Low Stock',
            self::CriticalStock => 'Critical Stock',
            self::OutOfStock => 'Out of Stock',
            self::BackInStock => 'Back in Stock',
        };
    }

    public function severity(): string
    {
        return match ($this) {
            self::LowStock => 'warning',
            self::CriticalStock => 'danger',
            self::OutOfStock => 'danger',
            self::BackInStock => 'success',
        };
    }
}
