<?php

return [
    [
        'key' => 'inventory-plus',
        'name' => 'inventory-plus::app.admin.acl.inventory-plus',
        'route' => 'admin.inventory-plus.movements.index',
        'sort' => 9,
    ],
    [
        'key' => 'inventory-plus.movements',
        'name' => 'inventory-plus::app.admin.acl.movements',
        'route' => 'admin.inventory-plus.movements.index',
        'sort' => 1,
    ],
    [
        'key' => 'inventory-plus.transfers',
        'name' => 'inventory-plus::app.admin.acl.transfers',
        'route' => 'admin.inventory-plus.transfers.index',
        'sort' => 2,
    ],
    [
        'key' => 'inventory-plus.barcode',
        'name' => 'inventory-plus::app.admin.acl.barcode',
        'route' => 'admin.inventory-plus.barcode.index',
        'sort' => 3,
    ],
    [
        'key' => 'inventory-plus.alerts',
        'name' => 'inventory-plus::app.admin.acl.alerts',
        'route' => 'admin.inventory-plus.alerts.index',
        'sort' => 4,
    ],
    [
        'key' => 'inventory-plus.import-export',
        'name' => 'inventory-plus::app.admin.acl.import-export',
        'route' => 'admin.inventory-plus.import-export.index',
        'sort' => 5,
    ],
];
