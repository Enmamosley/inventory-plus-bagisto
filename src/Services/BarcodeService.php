<?php

namespace Webkul\InventoryPlus\Services;

use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Webkul\InventoryPlus\Enums\BarcodeType;

class BarcodeService
{
    /**
     * Generate a barcode image as base64 PNG.
     */
    public function generatePng(string $value, BarcodeType $type = BarcodeType::CODE128, int $widthFactor = 2, int $height = 60): string
    {
        $generator = new BarcodeGeneratorPNG;
        $barcodeType = $this->mapType($type);

        $barcode = $generator->getBarcode($value, $barcodeType, $widthFactor, $height);

        return base64_encode($barcode);
    }

    /**
     * Generate a barcode as SVG string.
     */
    public function generateSvg(string $value, BarcodeType $type = BarcodeType::CODE128, int $widthFactor = 2, int $height = 60): string
    {
        $generator = new BarcodeGeneratorSVG;
        $barcodeType = $this->mapType($type);

        return $generator->getBarcode($value, $barcodeType, $widthFactor, $height);
    }

    /**
     * Generate a barcode label with product info for printing.
     *
     * @param  array{
     *     barcode: string,
     *     barcode_type: BarcodeType|string,
     *     product_name: string,
     *     sku: string,
     *     price?: string
     * }  $productData
     */
    public function generateLabel(array $productData): string
    {
        $type = $productData['barcode_type'] instanceof BarcodeType
            ? $productData['barcode_type']
            : BarcodeType::from($productData['barcode_type']);

        $barcodePng = $this->generatePng($productData['barcode'], $type, 2, 50);
        $barcodeImg = 'data:image/png;base64,' . $barcodePng;

        $name = e($productData['product_name']);
        $sku = e($productData['sku']);
        $barcode = e($productData['barcode']);
        $price = isset($productData['price']) ? e($productData['price']) : '';

        $priceHtml = $price !== '' ? "<div style=\"font-size: 11px; font-weight: bold; margin-top: 1mm;\">{$price}</div>" : '';

        return <<<HTML
        <div class="barcode-label" style="width: 58mm; padding: 3mm; border: 1px solid #ccc; font-family: Arial, sans-serif; text-align: center; page-break-inside: avoid;">
            <div style="font-size: 9px; font-weight: bold; margin-bottom: 2mm; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{$name}</div>
            <div style="margin: 2mm 0;"><img src="{$barcodeImg}" style="max-width: 100%; height: 40px;" alt="{$barcode}"></div>
            <div style="font-size: 10px; font-family: monospace; letter-spacing: 1px;">{$barcode}</div>
            <div style="font-size: 8px; color: #666; margin-top: 1mm;">SKU: {$sku}</div>
            {$priceHtml}
        </div>
        HTML;
    }

    /**
     * Generate printable sheet of barcode labels.
     *
     * @param  array<array>  $products
     */
    public function generateLabelSheet(array $products, int $columns = 3): string
    {
        $labels = array_map(fn ($p) => $this->generateLabel($p), $products);

        $rows = array_chunk($labels, $columns);
        $html = '<html><head><style>
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
            .label-grid { display: flex; flex-wrap: wrap; gap: 2mm; justify-content: flex-start; }
        </style></head><body>';

        $html .= '<div class="no-print" style="padding: 10px; text-align: center;">
            <button onclick="window.print()" style="padding: 8px 24px; font-size: 14px; cursor: pointer;">🖨️ Print Labels</button>
        </div>';

        $html .= '<div class="label-grid">';

        foreach ($labels as $label) {
            $html .= $label;
        }

        $html .= '</div></body></html>';

        return $html;
    }

    /**
     * Validate a barcode value for its type.
     */
    public function validate(string $value, BarcodeType $type): bool
    {
        return $type->validate($value);
    }

    /**
     * Generate EAN-13 check digit.
     */
    public function generateEan13(string $prefix12): string
    {
        if (strlen($prefix12) !== 12 || ! ctype_digit($prefix12)) {
            throw new \InvalidArgumentException('EAN-13 prefix must be exactly 12 digits.');
        }

        $sum = 0;

        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $prefix12[$i];
            $sum += ($i % 2 === 0) ? $digit : $digit * 3;
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return $prefix12 . $checkDigit;
    }

    /**
     * Look up a product by its barcode value.
     */
    public function findProductByBarcode(string $barcode): ?\Webkul\Product\Models\Product
    {
        $attrId = \Illuminate\Support\Facades\DB::table('attributes')
            ->where('code', 'barcode')
            ->value('id');

        if (! $attrId) {
            return null;
        }

        $productId = \Illuminate\Support\Facades\DB::table('product_attribute_values')
            ->where('attribute_id', $attrId)
            ->where('text_value', $barcode)
            ->value('product_id');

        if (! $productId) {
            return null;
        }

        return \Webkul\Product\Models\Product::find($productId);
    }

    /**
     * Map BarcodeType enum to picqer/php-barcode-generator type constant.
     */
    private function mapType(BarcodeType $type): string
    {
        return match ($type) {
            BarcodeType::EAN13 => $this->getGeneratorType('TYPE_EAN_13'),
            BarcodeType::EAN8 => $this->getGeneratorType('TYPE_EAN_8'),
            BarcodeType::UPCA => $this->getGeneratorType('TYPE_UPC_A'),
            BarcodeType::UPCE => $this->getGeneratorType('TYPE_UPC_E'),
            BarcodeType::CODE128 => $this->getGeneratorType('TYPE_CODE_128'),
            BarcodeType::CODE39 => $this->getGeneratorType('TYPE_CODE_39'),
            BarcodeType::QR => $this->getGeneratorType('TYPE_CODE_128'), // QR fallback to Code128 for 1D
        };
    }

    private function getGeneratorType(string $constant): string
    {
        return constant(BarcodeGeneratorPNG::class . '::' . $constant);
    }
}
