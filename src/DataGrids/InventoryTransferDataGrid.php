<?php

namespace Webkul\InventoryPlus\DataGrids;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class InventoryTransferDataGrid extends DataGrid
{
    protected $primaryColumn = 'id';

    protected $sortOrder = 'desc';

    public function prepareQueryBuilder(): \Illuminate\Database\Query\Builder
    {
        $queryBuilder = DB::table('inventory_transfers')
            ->leftJoin('inventory_sources as src', 'src.id', '=', 'inventory_transfers.source_id')
            ->leftJoin('inventory_sources as dst', 'dst.id', '=', 'inventory_transfers.destination_id')
            ->select(
                'inventory_transfers.id',
                'inventory_transfers.reference_number',
                'src.name as source_name',
                'dst.name as destination_name',
                'inventory_transfers.status',
                'inventory_transfers.shipped_at',
                'inventory_transfers.received_at',
                'inventory_transfers.created_at'
            );

        $this->addFilter('id', 'inventory_transfers.id');
        $this->addFilter('reference_number', 'inventory_transfers.reference_number');
        $this->addFilter('status', 'inventory_transfers.status');
        $this->addFilter('created_at', 'inventory_transfers.created_at');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('inventory-plus::app.admin.transfers.id'),
            'type' => 'integer',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'reference_number',
            'label' => trans('inventory-plus::app.admin.transfers.reference'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'source_name',
            'label' => trans('inventory-plus::app.admin.transfers.from'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index' => 'destination_name',
            'label' => trans('inventory-plus::app.admin.transfers.to'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('inventory-plus::app.admin.transfers.status'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'closure' => fn ($row) => ucfirst(str_replace('_', ' ', $row->status)),
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('inventory-plus::app.admin.transfers.date'),
            'type' => 'date_range',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
        ]);
    }

    public function prepareActions(): void
    {
        $this->addAction([
            'icon' => 'icon-eye',
            'title' => trans('inventory-plus::app.admin.transfers.view'),
            'method' => 'GET',
            'url' => fn ($row) => route('admin.inventory-plus.transfers.view', $row->id),
        ]);
    }
}
