<?php

namespace Webkul\InventoryPlus\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webkul\InventoryPlus\DataGrids\InventoryMovementDataGrid;
use Webkul\InventoryPlus\Enums\MovementType;
use Webkul\InventoryPlus\Services\InventoryMovementService;

class InventoryMovementController extends Controller
{
    public function __construct(
        protected InventoryMovementService $movementService
    ) {}

    /**
     * List all inventory movements.
     */
    public function index(): mixed
    {
        if (request()->ajax()) {
            return datagrid(InventoryMovementDataGrid::class)->process();
        }

        return view('inventory-plus::admin.inventory-movements.index');
    }

    /**
     * Show form to manually record a movement.
     */
    public function create(): \Illuminate\View\View
    {
        $sources = \Webkul\Inventory\Models\InventorySource::where('status', 1)->get();
        $types = MovementType::cases();

        return view('inventory-plus::admin.inventory-movements.create', compact('sources', 'types'));
    }

    /**
     * Store a manual inventory movement.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'inventory_source_id' => 'required|exists:inventory_sources,id',
            'type' => 'required|string',
            'qty_change' => 'required|integer|not_in:0',
            'reason' => 'nullable|string|max:500',
        ]);

        $this->movementService->record([
            'product_id' => $request->product_id,
            'inventory_source_id' => $request->inventory_source_id,
            'type' => $request->type,
            'qty_change' => $request->qty_change,
            'reason' => $request->reason,
            'reference_type' => 'manual',
        ]);

        session()->flash('success', trans('inventory-plus::app.admin.movements.create-success'));

        return redirect()->route('admin.inventory-plus.movements.index');
    }

    /**
     * View movement history for a product.
     */
    public function productHistory(int $productId): \Illuminate\View\View
    {
        $movements = $this->movementService->getProductHistory($productId, limit: 100);
        $product = \Webkul\Product\Models\Product::findOrFail($productId);

        return view('inventory-plus::admin.inventory-movements.product-history', compact('movements', 'product'));
    }
}
