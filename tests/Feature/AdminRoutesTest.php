<?php

use Webkul\User\Models\Admin;

test('movements index page loads for admin', function () {
    $this->loginAsAdmin();

    $response = $this->get(route('admin.inventory-plus.movements.index'));

    $response->assertStatus(200);
});

test('movements create page loads for admin', function () {
    $this->loginAsAdmin();

    $response = $this->get(route('admin.inventory-plus.movements.create'));

    $response->assertStatus(200);
});

test('transfers index page loads for admin', function () {
    $this->loginAsAdmin();

    $response = $this->get(route('admin.inventory-plus.transfers.index'));

    $response->assertStatus(200);
});

test('transfers create page loads for admin', function () {
    $this->loginAsAdmin();

    $response = $this->get(route('admin.inventory-plus.transfers.create'));

    $response->assertStatus(200);
});

test('barcode index page loads for admin', function () {
    $this->loginAsAdmin();

    $response = $this->get(route('admin.inventory-plus.barcode.index'));

    $response->assertStatus(200);
});

test('stock alerts index page loads for admin', function () {
    $this->loginAsAdmin();

    $response = $this->get(route('admin.inventory-plus.alerts.index'));

    $response->assertStatus(200);
});

test('stock alerts create page loads for admin', function () {
    $this->loginAsAdmin();

    $response = $this->get(route('admin.inventory-plus.alerts.create'));

    $response->assertStatus(200);
});

test('import export index page loads for admin', function () {
    $this->loginAsAdmin();

    $response = $this->get(route('admin.inventory-plus.import-export.index'));

    $response->assertStatus(200);
});

test('unauthenticated user is redirected from movements', function () {
    $response = $this->get(route('admin.inventory-plus.movements.index'));

    $response->assertRedirect();
});

test('import export template can be downloaded', function () {
    $this->loginAsAdmin();

    $response = $this->get(route('admin.inventory-plus.import-export.template'));

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
});

test('barcode update-stock route validates required fields', function () {
    $this->loginAsAdmin();

    $response = $this->postJson(route('admin.inventory-plus.barcode.update-stock'), []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['product_id', 'inventory_source_id', 'action', 'qty']);
});

test('barcode update-stock route rejects invalid action', function () {
    $this->loginAsAdmin();

    $response = $this->postJson(route('admin.inventory-plus.barcode.update-stock'), [
        'product_id'          => 1,
        'inventory_source_id' => 1,
        'action'              => 'invalid',
        'qty'                 => 5,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['action']);
});
