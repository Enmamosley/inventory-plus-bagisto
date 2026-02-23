<?php

namespace Webkul\InventoryPlus\DataGrids;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class InventoryMovementDataGrid extends DataGrid
{
    protected $primaryColumn = 'id';

    protected $sortOrder = 'desc';

    public function prepareQueryBuilder(): \Illuminate\Database\Query\Builder
    {
        $queryBuilder = DB::table('inventory_movements')
            ->leftJoin('products', 'products.id', '=', 'inventory_movements.product_id')
            ->leftJoin('inventory_sources', 'inventory_sources.id', '=', 'inventory_movements.inventory_source_id')
            ->select(
                'inventory_movements.id',
                'products.sku',
                'inventory_sources.name as source_name',
                'inventory_movements.type',
                'inventory_movements.qty_before',
                'inventory_movements.qty_change',
                'inventory_movements.qty_after',
                'inventory_movements.reason',
                'inventory_movements.created_at'
            );

        $this->addFilter('id', 'inventory_movements.id');
        $this->addFilter('sku', 'products.sku');
        $this->addFilter('type', 'inventory_movements.type');
        $this->addFilter('created_at', 'inventory_movements.created_at');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('inventory-plus::app.admin.movements.id'),
            'type' => 'integer',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'sku',
            'label' => trans('inventory-plus::app.admin.movements.sku'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'source_name',
            'label' => trans('inventory-plus::app.admin.movements.source'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index' => 'type',
            'label' => trans('inventory-plus::app.admin.movements.type'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'closure' => fn ($row) => ucfirst(str_replace('_', ' ', $row->type)),
        ]);

        $this->addColumn([
            'index' => 'qty_change',
            'label' => trans('inventory-plus::app.admin.movements.qty-change'),
            'type' => 'integer',
            'searchable' => false,
            'sortable' => true,
            'filterable' => false,
            'closure' => fn ($row) => ($row->qty_change > 0 ? '+' : '') . $row->qty_change,
        ]);

        $this->addColumn([
            'index' => 'qty_after',
            'label' => trans('inventory-plus::app.admin.movements.qty-after'),
            'type' => 'integer',
            'searchable' => false,
            'sortable' => true,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index' => 'reason',
            'label' => trans('inventory-plus::app.admin.movements.reason'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => false,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('inventory-plus::app.admin.movements.date'),
            'type' => 'date_range',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
        ]);
    }

    public function prepareActions(): void
    {
        // Read-only grid — no actions
    }
}
