<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .alert-box { padding: 15px; border-radius: 5px; margin: 10px 0; }
        .alert-warning { background: #fff3cd; border: 1px solid #ffc107; }
        .alert-danger { background: #f8d7da; border: 1px solid #dc3545; }
        .alert-success { background: #d4edda; border: 1px solid #28a745; }
        .info-table { border-collapse: collapse; width: 100%; margin: 15px 0; }
        .info-table td { padding: 8px 12px; border-bottom: 1px solid #eee; }
        .info-table td:first-child { font-weight: bold; width: 40%; color: #666; }
    </style>
</head>
<body>
    <h2>⚠️ Stock Alert Notification</h2>

    <div class="alert-box alert-{{ $alert->alert_type->severity() }}">
        <strong>{{ $alert->alert_type->label() }}</strong>
    </div>

    <table class="info-table">
        <tr>
            <td>Product</td>
            <td>{{ $product?->name ?? 'Product #' . $alert->product_id }}</td>
        </tr>
        <tr>
            <td>SKU</td>
            <td>{{ $product?->sku ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td>Current Quantity</td>
            <td><strong>{{ $alert->current_qty }}</strong></td>
        </tr>
        <tr>
            <td>Threshold</td>
            <td>{{ $alert->threshold }}</td>
        </tr>
        @if($rule)
        <tr>
            <td>Alert Rule</td>
            <td>{{ $rule->name }}</td>
        </tr>
        @endif
        <tr>
            <td>Date</td>
            <td>{{ $alert->created_at->format('Y-m-d H:i:s') }}</td>
        </tr>
    </table>

    <p>Please review and take action as needed.</p>

    <p style="color: #999; font-size: 12px;">
        This is an automated notification from Inventory Plus for Bagisto.
    </p>
</body>
</html>
