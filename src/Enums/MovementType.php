<?php

namespace Webkul\InventoryPlus\Enums;

enum MovementType: string
{
    case Receipt = 'receipt';
    case Sale = 'sale';
    case Adjustment = 'adjustment';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';
    case Return = 'return';
    case Initial = 'initial';
    case Import = 'import';

    public function label(): string
    {
        return match ($this) {
            self::Receipt => 'Receipt',
            self::Sale => 'Sale',
            self::Adjustment => 'Adjustment',
            self::TransferIn => 'Transfer In',
            self::TransferOut => 'Transfer Out',
            self::Return => 'Return',
            self::Initial => 'Initial Stock',
            self::Import => 'CSV Import',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Receipt => 'icon-arrow-down',
            self::Sale => 'icon-arrow-up',
            self::Adjustment => 'icon-edit',
            self::TransferIn => 'icon-arrow-down',
            self::TransferOut => 'icon-arrow-up',
            self::Return => 'icon-arrow-down',
            self::Initial => 'icon-star',
            self::Import => 'icon-import',
        };
    }
}
