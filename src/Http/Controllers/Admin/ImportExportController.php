<?php

namespace Webkul\InventoryPlus\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webkul\InventoryPlus\Services\CsvImportExportService;

class ImportExportController extends Controller
{
    public function __construct(
        protected CsvImportExportService $csvService
    ) {}

    /**
     * Show import/export page.
     */
    public function index(): \Illuminate\View\View
    {
        $sources = \Webkul\Inventory\Models\InventorySource::where('status', 1)->get();

        return view('inventory-plus::admin.import-export.index', compact('sources'));
    }

    /**
     * Export inventory as CSV.
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $sourceId = $request->input('inventory_source_id');
        $csv = $this->csvService->exportInventory($sourceId ? (int) $sourceId : null);
        $filename = 'inventory-export-'.date('Y-m-d-His').'.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Import inventory from CSV.
     */
    public function import(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'action' => 'required|in:set,add',
        ]);

        $result = $this->csvService->importInventory(
            $request->file('file'),
            $request->input('action', 'set')
        );

        if (! empty($result['errors'])) {
            session()->flash('warning', trans('inventory-plus::app.admin.import-export.partial-success', [
                'imported' => $result['imported'],
                'errors' => count($result['errors']),
            ]));
            session()->flash('import_result', $result);
        } else {
            session()->flash('success', trans('inventory-plus::app.admin.import-export.success', [
                'count' => $result['imported'],
            ]));
        }

        return redirect()->route('admin.inventory-plus.import-export.index');
    }

    /**
     * Download CSV template.
     */
    public function template(): \Symfony\Component\HttpFoundation\Response
    {
        $csv = $this->csvService->getImportTemplate();

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="inventory-import-template.csv"');
    }
}
