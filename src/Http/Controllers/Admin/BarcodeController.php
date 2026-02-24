<?php

namespace Webkul\InventoryPlus\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webkul\Category\Models\Category;
use Webkul\InventoryPlus\Enums\BarcodeType;
use Webkul\InventoryPlus\Services\BarcodeService;
use Webkul\InventoryPlus\Services\InventoryMovementService;

class BarcodeController extends Controller
{
    public function __construct(
        protected BarcodeService $barcodeService,
        protected InventoryMovementService $movementService
    ) {}

    /**
     * Barcode scanner / lookup page.
     */
    public function index(): \Illuminate\View\View
    {
        $locale = app()->getLocale();

        $categories = Category::with(['translations'])
            ->whereNotNull('parent_id')
            ->get()
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->translate($locale)?->name ?? $cat->translate('en')?->name ?? "Category #{$cat->id}",
            ]);

        return view('inventory-plus::admin.barcode.index', compact('categories'));
    }

    /**
     * Search products by name, SKU, or barcode (AJAX).
     */
    public function searchProducts(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'query' => 'nullable|string|max:200',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        $search = $request->input('query', '');
        $categoryId = $request->input('category_id');
        $locale = app()->getLocale();
        $channel = core()->getCurrentChannelCode();

        $query = \Webkul\Product\Models\ProductFlat::query()
            ->select('product_flat.product_id', 'product_flat.sku', 'product_flat.name', 'product_flat.price')
            ->where('product_flat.locale', $locale)
            ->where('product_flat.channel', $channel)
            ->whereNotNull('product_flat.name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_flat.name', 'LIKE', "%{$search}%")
                    ->orWhere('product_flat.sku', 'LIKE', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->join('product_categories', 'product_categories.product_id', '=', 'product_flat.product_id')
                ->where('product_categories.category_id', $categoryId);
        }

        $products = $query->distinct()
            ->limit(30)
            ->get();

        // Eager load barcode from EAV on parent products
        $productIds = $products->pluck('product_id')->toArray();
        $parentProducts = \Webkul\Product\Models\Product::whereIn('id', $productIds)->get()->keyBy('id');

        $results = $products->map(function ($flat) use ($parentProducts) {
            $parent = $parentProducts->get($flat->product_id);

            return [
                'id' => $flat->product_id,
                'sku' => $flat->sku,
                'name' => $flat->name,
                'price' => $flat->price ? core()->currency($flat->price) : null,
                'barcode' => $parent?->barcode,
                'barcode_type' => $parent?->barcode_type,
            ];
        });

        return response()->json(['products' => $results->values()]);
    }

    /**
     * Look up a product by barcode (AJAX).
     */
    public function lookup(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['barcode' => 'required|string']);

        $product = $this->barcodeService->findProductByBarcode($request->barcode);

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => trans('inventory-plus::app.admin.barcode.not-found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'barcode_type' => $product->barcode_type,
                'inventories' => $product->inventories->map(fn ($inv) => [
                    'source_id' => $inv->inventory_source_id,
                    'source_name' => $inv->inventory_source?->name,
                    'qty' => $inv->qty,
                ]),
            ],
        ]);
    }

    /**
     * Quick stock update from barcode scanner (AJAX).
     */
    public function updateStock(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'inventory_source_id' => 'required|integer|exists:inventory_sources,id',
            'action' => 'required|in:set,add,subtract',
            'qty' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        $result = $this->movementService->recordAdjustment([
            'product_id' => $request->input('product_id'),
            'inventory_source_id' => $request->input('inventory_source_id'),
            'action' => $request->input('action'),
            'qty' => (int) $request->input('qty'),
            'reason' => $request->input('reason'),
            'user_id' => auth()->guard('admin')->id(),
        ]);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => trans('inventory-plus::app.admin.barcode.'.$result['message']),
            ], 422);
        }

        $message = $result['message'] === 'no-change'
            ? trans('inventory-plus::app.admin.barcode.no-change')
            : trans('inventory-plus::app.admin.barcode.update-success', ['qty' => $result['new_qty']]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'new_qty' => $result['new_qty'],
        ]);
    }

    /**
     * Generate barcode image for a product.
     */
    public function generate(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'value' => 'required|string',
            'type' => 'required|string',
            'format' => 'nullable|in:png,svg',
        ]);

        $type = BarcodeType::from($request->type);
        $format = $request->input('format', 'png');

        if ($format === 'svg') {
            $barcode = $this->barcodeService->generateSvg($request->value, $type);
        } else {
            $barcode = $this->barcodeService->generatePng($request->value, $type);
        }

        return response()->json([
            'success' => true,
            'format' => $format,
            'barcode' => $format === 'png' ? 'data:image/png;base64,'.$barcode : $barcode,
        ]);
    }

    /**
     * Print barcode labels for selected products.
     */
    public function printLabels(Request $request): \Illuminate\Http\Response
    {
        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'copies' => 'nullable|integer|min:1|max:100',
        ]);

        $copies = $request->input('copies', 1);
        $products = \Webkul\Product\Models\Product::whereIn('id', $request->product_ids)->get();

        $labelData = [];

        foreach ($products as $product) {
            $barcode = $product->barcode;

            if (! $barcode) {
                continue;
            }

            $barcodeType = $product->barcode_type ?: 'CODE-128';

            for ($i = 0; $i < $copies; $i++) {
                $labelData[] = [
                    'barcode' => $barcode,
                    'barcode_type' => $barcodeType,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'price' => core()->currency($product->price),
                ];
            }
        }

        if (empty($labelData)) {
            return response('No products with barcodes found.', 422);
        }

        $html = $this->barcodeService->generateLabelSheet($labelData);

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Auto-generate EAN-13 for a product.
     */
    public function autoGenerate(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'prefix' => 'nullable|string|max:7',
        ]);

        $prefix = $request->input('prefix', '200'); // 200-299 is for internal use
        $product = \Webkul\Product\Models\Product::findOrFail($request->product_id);

        // Pad product ID to fill 12 digits
        $paddedId = str_pad((string) $product->id, 12 - strlen($prefix), '0', STR_PAD_LEFT);
        $prefix12 = $prefix.$paddedId;

        // Truncate to 12 exactly
        $prefix12 = substr($prefix12, 0, 12);

        $ean13 = $this->barcodeService->generateEan13($prefix12);

        return response()->json([
            'success' => true,
            'barcode' => $ean13,
            'type' => 'EAN-13',
        ]);
    }
}
