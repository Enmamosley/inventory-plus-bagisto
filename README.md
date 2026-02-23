# InventoryPlus for Bagisto 2.x

Advanced inventory management module for [Bagisto](https://bagisto.com) e-commerce platform, featuring barcode support, stock transfers, movement tracking, alerts, and CSV import/export.

## Features

- **Inventory Movement Log** — Track every stock change (sales, receipts, adjustments, transfers, returns, imports) with full audit trail
- **Stock Transfers** — Move inventory between warehouses with ship/receive workflow and reference tracking
- **Barcode Management** — Generate, scan, and print barcode labels (EAN-13, EAN-8, UPC-A, UPC-E, Code 128, Code 39)
- **Stock Alerts** — Configurable low/critical stock threshold rules with email notifications
- **CSV Import/Export** — Bulk update inventory via CSV files with set/add actions

## Requirements

- Bagisto 2.x
- PHP 8.2+
- MySQL 8.0+

## Installation

### 1. Copy the package

```bash
cp -r packages/Webkul/InventoryPlus /path/to/bagisto/packages/Webkul/InventoryPlus
```

### 2. Register the namespace

Add to your `composer.json` autoload:

```json
"Webkul\\InventoryPlus\\": "packages/Webkul/InventoryPlus/src"
```

### 3. Register the service provider

Add to `bootstrap/providers.php`:

```php
Webkul\InventoryPlus\Providers\InventoryPlusServiceProvider::class,
```

### 4. Install dependencies

```bash
composer require picqer/php-barcode-generator:^3.0
composer dump-autoload
```

### 5. Run migrations

```bash
php artisan migrate
```

This creates 5 tables and adds barcode/barcode_type EAV attributes to all product families.

## Admin Panel

Once installed, a new **Inventory Plus** menu appears in the admin sidebar with:

| Section | URL | Description |
|---------|-----|-------------|
| Movements | `/admin/inventory-plus/movements` | View/record inventory movements |
| Transfers | `/admin/inventory-plus/transfers` | Create and manage stock transfers |
| Barcode | `/admin/inventory-plus/barcode` | Scan barcodes, generate labels |
| Alerts | `/admin/inventory-plus/alerts` | Configure stock alert rules |
| Import/Export | `/admin/inventory-plus/import-export` | Bulk CSV operations |

## Barcode Support

Products gain two new attributes in the admin product form:
- **Barcode** — The barcode value (EAN-13, UPC-A, etc.)
- **Barcode Type** — Selectable type (EAN-13, EAN-8, UPC-A, UPC-E, Code 128, Code 39, QR)

The barcode scanner page supports:
- Manual barcode entry or USB barcode scanner input
- Live product lookup via AJAX
- On-the-fly barcode generation and preview
- Batch label printing (customizable copies per product)
- Auto EAN-13 generation with check digit calculation

## Stock Alerts

Configure rules with:
- Per-product or global scope
- Per-source or all sources
- Customizable low/critical stock thresholds
- Email notifications to multiple recipients
- Automated hourly checks via `inventory-plus:check-alerts` command

## CSV Import Format

```csv
sku,source_code,qty,action
PROD-001,default,50,set
PROD-002,warehouse-1,+10,add
```

- **set**: Replace current quantity
- **add**: Add to current quantity

## Testing

```bash
php artisan test packages/Webkul/InventoryPlus/tests/
```

## License

MIT

## Author

Enmanoell Mosley — enma@mosley.mx
