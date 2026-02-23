<?php

namespace Webkul\InventoryPlus\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webkul\InventoryPlus\Models\StockAlertLog;
use Webkul\InventoryPlus\Models\StockAlertRule;
use Webkul\InventoryPlus\Services\StockAlertService;

class StockAlertController extends Controller
{
    public function __construct(
        protected StockAlertService $alertService
    ) {}

    /**
     * List alert rules and recent logs.
     */
    public function index(): \Illuminate\View\View
    {
        $rules = StockAlertRule::with('product')->orderByDesc('created_at')->get();
        $recentAlerts = StockAlertLog::with('product')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('inventory-plus::admin.stock-alerts.index', compact('rules', 'recentAlerts'));
    }

    /**
     * Show create rule form.
     */
    public function create(): \Illuminate\View\View
    {
        $sources = \Webkul\Inventory\Models\InventorySource::where('status', 1)->get();
        $products = \Webkul\Product\Models\ProductFlat::select('product_id as id', 'name', 'sku')
            ->where('locale', config('app.locale', 'en'))
            ->orderBy('name')
            ->get();

        return view('inventory-plus::admin.stock-alerts.create', compact('sources', 'products'));
    }

    /**
     * Store new alert rule.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'product_id' => 'nullable|exists:products,id',
            'inventory_source_id' => 'nullable|exists:inventory_sources,id',
            'low_stock_threshold' => 'required|integer|min:1',
            'critical_stock_threshold' => 'required|integer|min:0',
            'notify_email' => 'boolean',
            'email_recipients' => 'required_if:notify_email,1|nullable|string|max:500',
        ]);

        StockAlertRule::create($request->only([
            'name', 'product_id', 'inventory_source_id',
            'low_stock_threshold', 'critical_stock_threshold',
            'notify_email', 'email_recipients',
        ]));

        session()->flash('success', trans('inventory-plus::app.admin.stock-alerts.create-success'));

        return redirect()->route('admin.inventory-plus.alerts.index');
    }

    /**
     * Delete an alert rule.
     */
    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        StockAlertRule::findOrFail($id)->delete();

        session()->flash('success', trans('inventory-plus::app.admin.stock-alerts.delete-success'));

        return redirect()->route('admin.inventory-plus.alerts.index');
    }

    /**
     * Manually trigger alert check.
     */
    public function check(): \Illuminate\Http\RedirectResponse
    {
        $count = $this->alertService->checkAlerts();

        session()->flash('success', trans('inventory-plus::app.admin.stock-alerts.check-complete', ['count' => $count]));

        return redirect()->route('admin.inventory-plus.alerts.index');
    }
}
