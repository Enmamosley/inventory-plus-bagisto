<?php

namespace Webkul\InventoryPlus\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webkul\InventoryPlus\DataGrids\InventoryTransferDataGrid;
use Webkul\InventoryPlus\Models\InventoryTransfer;
use Webkul\InventoryPlus\Services\TransferService;

class TransferController extends Controller
{
    public function __construct(
        protected TransferService $transferService
    ) {}

    /**
     * List all transfers.
     */
    public function index(): mixed
    {
        if (request()->ajax()) {
            return datagrid(InventoryTransferDataGrid::class)->process();
        }

        return view('inventory-plus::admin.transfers.index');
    }

    /**
     * Show create transfer form.
     */
    public function create(): \Illuminate\View\View
    {
        $sources = \Webkul\Inventory\Models\InventorySource::where('status', 1)->get();

        return view('inventory-plus::admin.transfers.create', compact('sources'));
    }

    /**
     * Store new transfer.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'source_id' => 'required|exists:inventory_sources,id',
            'destination_id' => 'required|exists:inventory_sources,id|different:source_id',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        $transfer = $this->transferService->create($request->only(['source_id', 'destination_id', 'notes', 'items']));

        session()->flash('success', trans('inventory-plus::app.admin.transfers.create-success'));

        return redirect()->route('admin.inventory-plus.transfers.view', $transfer->id);
    }

    /**
     * View transfer details.
     */
    public function view(int $id): \Illuminate\View\View
    {
        $transfer = InventoryTransfer::with(['items.product', 'source', 'destination'])->findOrFail($id);

        return view('inventory-plus::admin.transfers.view', compact('transfer'));
    }

    /**
     * Ship transfer.
     */
    public function ship(int $id): \Illuminate\Http\RedirectResponse
    {
        $transfer = InventoryTransfer::findOrFail($id);

        try {
            $this->transferService->ship($transfer);
            session()->flash('success', trans('inventory-plus::app.admin.transfers.shipped'));
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }

        return redirect()->route('admin.inventory-plus.transfers.view', $id);
    }

    /**
     * Receive transfer.
     */
    public function receive(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $transfer = InventoryTransfer::findOrFail($id);

        try {
            $receivedQtys = $request->input('received_qtys');
            $this->transferService->receive($transfer, $receivedQtys);
            session()->flash('success', trans('inventory-plus::app.admin.transfers.received'));
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }

        return redirect()->route('admin.inventory-plus.transfers.view', $id);
    }

    /**
     * Cancel transfer.
     */
    public function cancel(int $id): \Illuminate\Http\RedirectResponse
    {
        $transfer = InventoryTransfer::findOrFail($id);

        try {
            $this->transferService->cancel($transfer);
            session()->flash('success', trans('inventory-plus::app.admin.transfers.cancelled'));
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }

        return redirect()->route('admin.inventory-plus.transfers.view', $id);
    }
}
