<?php

namespace Webkul\InventoryPlus\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Webkul\InventoryPlus\Enums\MovementType;
use Webkul\Product\Models\Product;

class CsvImportExportService
{
    public function __construct(
        protected InventoryMovementService $movementService
    ) {}

    /**
     * Export inventory data as CSV string.
     */
    public function exportInventory(?int $sourceId = null): string
    {
        $query = DB::table('product_inventories')
            ->join('products', 'products.id', '=', 'product_inventories.product_id')
            ->join('inventory_sources', 'inventory_sources.id', '=', 'product_inventories.inventory_source_id')
            ->select(
                'products.sku',
                'inventory_sources.code as source_code',
                'inventory_sources.name as source_name',
                'product_inventories.qty'
            );

        // Get barcode via EAV
        $barcodeAttrId = DB::table('attributes')->where('code', 'barcode')->value('id');

        if ($sourceId) {
            $query->where('product_inventories.inventory_source_id', $sourceId);
        }

        $rows = $query->orderBy('products.sku')->get();

        $csv = "sku,barcode,source_code,source_name,qty\n";

        foreach ($rows as $row) {
            $barcode = '';

            if ($barcodeAttrId) {
                $barcode = DB::table('product_attribute_values')
                    ->where('product_id', function ($q) use ($row) {
                        $q->select('id')->from('products')->where('sku', $row->sku)->limit(1);
                    })
                    ->where('attribute_id', $barcodeAttrId)
                    ->value('text_value') ?? '';
            }

            $csv .= sprintf(
                "%s,%s,%s,%s,%d\n",
                $this->escapeCsv($row->sku),
                $this->escapeCsv($barcode),
                $this->escapeCsv($row->source_code),
                $this->escapeCsv($row->source_name),
                $row->qty
            );
        }

        return $csv;
    }

    /**
     * Import inventory from CSV. Returns summary.
     *
     * Expected columns: sku, source_code, qty, [action]
     * Action: "set" (default) = set qty to value, "add" = add to current qty
     *
     * @return array{imported: int, errors: array<string>}
     */
    public function importInventory(UploadedFile $file, string $defaultAction = 'set'): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if (! $handle) {
            return ['imported' => 0, 'errors' => ['Could not open file.']];
        }

        $header = fgetcsv($handle);

        if (! $header) {
            fclose($handle);

            return ['imported' => 0, 'errors' => ['Empty file or invalid CSV format.']];
        }

        $header = array_map('strtolower', array_map('trim', $header));
        $skuIdx = array_search('sku', $header);
        $sourceIdx = array_search('source_code', $header);
        $qtyIdx = array_search('qty', $header);
        $actionIdx = array_search('action', $header);

        if ($skuIdx === false || $qtyIdx === false) {
            fclose($handle);

            return ['imported' => 0, 'errors' => ['CSV must have "sku" and "qty" columns.']];
        }

        $imported = 0;
        $errors = [];
        $line = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $line++;

            $sku = trim($row[$skuIdx] ?? '');
            $sourceCode = $sourceIdx !== false ? trim($row[$sourceIdx] ?? '') : '';
            $qty = (int) ($row[$qtyIdx] ?? 0);
            $action = $actionIdx !== false ? trim($row[$actionIdx] ?? '') : $defaultAction;

            if (empty($sku)) {
                $errors[] = "Line {$line}: Missing SKU.";

                continue;
            }

            $product = Product::where('sku', $sku)->first();

            if (! $product) {
                $errors[] = "Line {$line}: Product SKU '{$sku}' not found.";

                continue;
            }

            // Find inventory source
            $sourceId = null;

            if ($sourceCode) {
                $sourceId = DB::table('inventory_sources')->where('code', $sourceCode)->value('id');

                if (! $sourceId) {
                    $errors[] = "Line {$line}: Inventory source '{$sourceCode}' not found.";

                    continue;
                }
            } else {
                // Use first active source
                $sourceId = DB::table('inventory_sources')
                    ->where('status', 1)
                    ->orderBy('priority')
                    ->value('id');

                if (! $sourceId) {
                    $errors[] = "Line {$line}: No active inventory source found.";

                    continue;
                }
            }

            try {
                $currentQty = DB::table('product_inventories')
                    ->where('product_id', $product->id)
                    ->where('inventory_source_id', $sourceId)
                    ->value('qty') ?? 0;

                $qtyChange = $action === 'add' ? $qty : ($qty - $currentQty);

                if ($qtyChange !== 0) {
                    $this->movementService->record([
                        'product_id' => $product->id,
                        'inventory_source_id' => $sourceId,
                        'type' => MovementType::Import,
                        'qty_change' => $qtyChange,
                        'reason' => "CSV import (action: {$action})",
                        'reference_type' => 'import',
                    ]);
                }

                $imported++;
            } catch (\Throwable $e) {
                $errors[] = "Line {$line}: ".$e->getMessage();
            }
        }

        fclose($handle);

        return ['imported' => $imported, 'errors' => $errors];
    }

    /**
     * Generate a CSV template for import.
     */
    public function getImportTemplate(): string
    {
        return "sku,source_code,qty,action\nPRODUCT-SKU,default,100,set\n";
    }

    private function escapeCsv(string $value): string
    {
        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"'.str_replace('"', '""', $value).'"';
        }

        return $value;
    }
}
