<?php

namespace Webkul\InventoryPlus\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Webkul\InventoryPlus\Enums\AlertType;
use Webkul\InventoryPlus\Mail\StockAlertMail;
use Webkul\InventoryPlus\Models\StockAlertLog;
use Webkul\InventoryPlus\Models\StockAlertRule;
use Webkul\Product\Models\ProductInventory;

class StockAlertService
{
    /**
     * Check stock levels and trigger alerts.
     */
    public function checkAlerts(): int
    {
        $alertCount = 0;
        $rules = StockAlertRule::where('is_active', true)->get();

        foreach ($rules as $rule) {
            $alertCount += $this->evaluateRule($rule);
        }

        return $alertCount;
    }

    /**
     * Check a specific product's stock against all active rules.
     */
    public function checkProductAlerts(int $productId): int
    {
        $alertCount = 0;

        // Check product-specific rules
        $rules = StockAlertRule::where('is_active', true)
            ->where(function ($q) use ($productId) {
                $q->where('product_id', $productId)
                    ->orWhereNull('product_id');
            })
            ->get();

        foreach ($rules as $rule) {
            $alertCount += $this->evaluateRule($rule, $productId);
        }

        return $alertCount;
    }

    /**
     * Evaluate a single rule and create alerts if needed.
     */
    private function evaluateRule(StockAlertRule $rule, ?int $specificProductId = null): int
    {
        $alertCount = 0;

        $query = ProductInventory::query();

        if ($specificProductId) {
            $query->where('product_id', $specificProductId);
        } elseif ($rule->product_id) {
            $query->where('product_id', $rule->product_id);
        }

        if ($rule->inventory_source_id) {
            $query->where('inventory_source_id', $rule->inventory_source_id);
        }

        // Group by product to get total stock
        $stocks = $query->select('product_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_id')
            ->get();

        foreach ($stocks as $stock) {
            $qty = (int) $stock->total_qty;
            $alertType = $this->determineAlertType($qty, $rule);

            if (! $alertType) {
                continue;
            }

            // Avoid duplicate alerts within last 24 hours
            $recentAlert = StockAlertLog::where('product_id', $stock->product_id)
                ->where('alert_type', $alertType)
                ->where('created_at', '>=', now()->subDay())
                ->exists();

            if ($recentAlert) {
                continue;
            }

            $log = StockAlertLog::create([
                'rule_id' => $rule->id,
                'product_id' => $stock->product_id,
                'inventory_source_id' => $rule->inventory_source_id,
                'alert_type' => $alertType,
                'current_qty' => $qty,
                'threshold' => $alertType === AlertType::OutOfStock
                    ? 0
                    : ($alertType === AlertType::CriticalStock ? $rule->critical_stock_threshold : $rule->low_stock_threshold),
            ]);

            if ($rule->notify_email && $rule->email_recipients) {
                $this->sendNotification($log, $rule);
            }

            $alertCount++;
        }

        return $alertCount;
    }

    private function determineAlertType(int $qty, StockAlertRule $rule): ?AlertType
    {
        if ($qty <= 0) {
            return AlertType::OutOfStock;
        }

        if ($rule->critical_stock_threshold > 0 && $qty <= $rule->critical_stock_threshold) {
            return AlertType::CriticalStock;
        }

        if ($qty <= $rule->low_stock_threshold) {
            return AlertType::LowStock;
        }

        return null;
    }

    private function sendNotification(StockAlertLog $log, StockAlertRule $rule): void
    {
        $recipients = array_filter(array_map('trim', explode(',', $rule->email_recipients)));

        if (empty($recipients)) {
            return;
        }

        try {
            foreach ($recipients as $email) {
                Mail::to($email)->queue(new StockAlertMail($log));
            }

            $log->update([
                'notified' => true,
                'notified_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
