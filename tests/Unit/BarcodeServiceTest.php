<?php

use Webkul\InventoryPlus\Enums\BarcodeType;
use Webkul\InventoryPlus\Services\BarcodeService;

beforeEach(function () {
    $this->barcodeService = new BarcodeService;
});

test('generate png returns base64 encoded string', function () {
    $result = $this->barcodeService->generatePng('12345', BarcodeType::CODE128);

    expect($result)->toBeString()
        ->and(base64_decode($result, true))->not->toBeFalse();
});

test('generate svg returns svg markup', function () {
    $result = $this->barcodeService->generateSvg('12345', BarcodeType::CODE128);

    expect($result)->toBeString()
        ->and($result)->toContain('<svg')
        ->and($result)->toContain('</svg>');
});

test('generate ean13 produces valid check digit', function () {
    // Known EAN-13: 4006381333931
    $result = $this->barcodeService->generateEan13('400638133393');

    expect($result)->toBe('4006381333931')
        ->and(strlen($result))->toBe(13);
});

test('generate ean13 throws for invalid prefix', function () {
    $this->barcodeService->generateEan13('123');
})->throws(\InvalidArgumentException::class);

test('generate ean13 throws for non-digit prefix', function () {
    $this->barcodeService->generateEan13('abcdefghijkl');
})->throws(\InvalidArgumentException::class);

test('validate delegates to barcode type enum', function () {
    expect($this->barcodeService->validate('1234567890123', BarcodeType::EAN13))->toBeTrue()
        ->and($this->barcodeService->validate('short', BarcodeType::EAN13))->toBeFalse()
        ->and($this->barcodeService->validate('HELLO', BarcodeType::CODE128))->toBeTrue();
});

test('generate label returns html with product data', function () {
    $html = $this->barcodeService->generateLabel([
        'barcode' => 'TEST123456',
        'barcode_type' => BarcodeType::CODE128,
        'product_name' => 'Test Product',
        'sku' => 'SKU-001',
        'price' => '$19.99',
    ]);

    expect($html)->toBeString()
        ->and($html)->toContain('Test Product')
        ->and($html)->toContain('SKU-001')
        ->and($html)->toContain('TEST123456')
        ->and($html)->toContain('$19.99')
        ->and($html)->toContain('data:image/png;base64,');
});

test('generate label sheet returns printable html page', function () {
    $products = [
        [
            'barcode' => 'CODE001',
            'barcode_type' => BarcodeType::CODE128,
            'product_name' => 'Product A',
            'sku' => 'SKU-A',
        ],
        [
            'barcode' => 'CODE002',
            'barcode_type' => BarcodeType::CODE128,
            'product_name' => 'Product B',
            'sku' => 'SKU-B',
        ],
    ];

    $html = $this->barcodeService->generateLabelSheet($products, 3);

    expect($html)->toBeString()
        ->and($html)->toContain('<html>')
        ->and($html)->toContain('Product A')
        ->and($html)->toContain('Product B')
        ->and($html)->toContain('window.print()');
});
