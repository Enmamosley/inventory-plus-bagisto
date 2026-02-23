<?php

use Webkul\InventoryPlus\Enums\AlertType;
use Webkul\InventoryPlus\Enums\BarcodeType;
use Webkul\InventoryPlus\Enums\MovementType;
use Webkul\InventoryPlus\Enums\TransferStatus;

/*
|--------------------------------------------------------------------------
| BarcodeType Enum Tests
|--------------------------------------------------------------------------
*/

test('barcode type has all expected cases', function () {
    $cases = BarcodeType::cases();

    expect($cases)->toHaveCount(7)
        ->and(BarcodeType::EAN13->value)->toBe('EAN-13')
        ->and(BarcodeType::EAN8->value)->toBe('EAN-8')
        ->and(BarcodeType::UPCA->value)->toBe('UPC-A')
        ->and(BarcodeType::UPCE->value)->toBe('UPC-E')
        ->and(BarcodeType::CODE128->value)->toBe('CODE-128')
        ->and(BarcodeType::CODE39->value)->toBe('CODE-39')
        ->and(BarcodeType::QR->value)->toBe('QR');
});

test('barcode type returns human-readable labels', function () {
    expect(BarcodeType::EAN13->label())->toBe('EAN-13')
        ->and(BarcodeType::CODE128->label())->toBe('Code 128')
        ->and(BarcodeType::QR->label())->toBe('QR Code');
});

test('barcode type validates EAN-13 correctly', function () {
    expect(BarcodeType::EAN13->validate('1234567890123'))->toBeTrue()
        ->and(BarcodeType::EAN13->validate('123456789012'))->toBeFalse()
        ->and(BarcodeType::EAN13->validate('12345678901234'))->toBeFalse()
        ->and(BarcodeType::EAN13->validate('abcdefghijklm'))->toBeFalse();
});

test('barcode type validates EAN-8 correctly', function () {
    expect(BarcodeType::EAN8->validate('12345678'))->toBeTrue()
        ->and(BarcodeType::EAN8->validate('1234567'))->toBeFalse()
        ->and(BarcodeType::EAN8->validate('abcdefgh'))->toBeFalse();
});

test('barcode type validates UPC-A correctly', function () {
    expect(BarcodeType::UPCA->validate('123456789012'))->toBeTrue()
        ->and(BarcodeType::UPCA->validate('1234567890123'))->toBeFalse();
});

test('barcode type validates Code 128 correctly', function () {
    expect(BarcodeType::CODE128->validate('HELLO-123'))->toBeTrue()
        ->and(BarcodeType::CODE128->validate(''))->toBeFalse();
});

test('barcode type validates QR correctly', function () {
    expect(BarcodeType::QR->validate('https://example.com'))->toBeTrue()
        ->and(BarcodeType::QR->validate(''))->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| MovementType Enum Tests
|--------------------------------------------------------------------------
*/

test('movement type has all expected cases', function () {
    $cases = MovementType::cases();

    expect($cases)->toHaveCount(8)
        ->and(MovementType::Receipt->value)->toBe('receipt')
        ->and(MovementType::Sale->value)->toBe('sale')
        ->and(MovementType::Adjustment->value)->toBe('adjustment')
        ->and(MovementType::TransferIn->value)->toBe('transfer_in')
        ->and(MovementType::TransferOut->value)->toBe('transfer_out')
        ->and(MovementType::Return->value)->toBe('return')
        ->and(MovementType::Initial->value)->toBe('initial')
        ->and(MovementType::Import->value)->toBe('import');
});

test('movement type returns labels', function () {
    expect(MovementType::Sale->label())->toBe('Sale')
        ->and(MovementType::TransferIn->label())->toBe('Transfer In')
        ->and(MovementType::Import->label())->toBe('CSV Import');
});

test('movement type returns icons', function () {
    expect(MovementType::Receipt->icon())->toBeString()
        ->and(MovementType::Sale->icon())->toBeString();
});

/*
|--------------------------------------------------------------------------
| TransferStatus Enum Tests
|--------------------------------------------------------------------------
*/

test('transfer status has all expected cases', function () {
    $cases = TransferStatus::cases();

    expect($cases)->toHaveCount(4)
        ->and(TransferStatus::Pending->value)->toBe('pending')
        ->and(TransferStatus::InTransit->value)->toBe('in_transit')
        ->and(TransferStatus::Completed->value)->toBe('completed')
        ->and(TransferStatus::Cancelled->value)->toBe('cancelled');
});

test('transfer status returns colors', function () {
    expect(TransferStatus::Pending->color())->toBe('warning')
        ->and(TransferStatus::Completed->color())->toBe('success')
        ->and(TransferStatus::Cancelled->color())->toBe('danger');
});

/*
|--------------------------------------------------------------------------
| AlertType Enum Tests
|--------------------------------------------------------------------------
*/

test('alert type has all expected cases', function () {
    $cases = AlertType::cases();

    expect($cases)->toHaveCount(4)
        ->and(AlertType::LowStock->value)->toBe('low_stock')
        ->and(AlertType::CriticalStock->value)->toBe('critical_stock')
        ->and(AlertType::OutOfStock->value)->toBe('out_of_stock')
        ->and(AlertType::BackInStock->value)->toBe('back_in_stock');
});

test('alert type returns severity levels', function () {
    expect(AlertType::LowStock->severity())->toBe('warning')
        ->and(AlertType::CriticalStock->severity())->toBe('danger')
        ->and(AlertType::OutOfStock->severity())->toBe('danger')
        ->and(AlertType::BackInStock->severity())->toBe('success');
});
