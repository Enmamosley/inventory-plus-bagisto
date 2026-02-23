<?php

namespace Webkul\InventoryPlus\Enums;

enum BarcodeType: string
{
    case EAN13 = 'EAN-13';
    case EAN8 = 'EAN-8';
    case UPCA = 'UPC-A';
    case UPCE = 'UPC-E';
    case CODE128 = 'CODE-128';
    case CODE39 = 'CODE-39';
    case QR = 'QR';

    public function label(): string
    {
        return match ($this) {
            self::EAN13 => 'EAN-13',
            self::EAN8 => 'EAN-8',
            self::UPCA => 'UPC-A',
            self::UPCE => 'UPC-E',
            self::CODE128 => 'Code 128',
            self::CODE39 => 'Code 39',
            self::QR => 'QR Code',
        };
    }

    /**
     * Validate a barcode value for this type.
     */
    public function validate(string $value): bool
    {
        return match ($this) {
            self::EAN13 => (bool) preg_match('/^\d{13}$/', $value),
            self::EAN8 => (bool) preg_match('/^\d{8}$/', $value),
            self::UPCA => (bool) preg_match('/^\d{12}$/', $value),
            self::UPCE => (bool) preg_match('/^\d{8}$/', $value),
            self::CODE128, self::CODE39 => strlen($value) > 0 && strlen($value) <= 128,
            self::QR => strlen($value) > 0 && strlen($value) <= 4296,
        };
    }
}
