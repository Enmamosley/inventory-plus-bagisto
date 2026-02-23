<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_alert_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('product_id')->nullable(); // null = global rule
            $table->unsignedInteger('inventory_source_id')->nullable(); // null = all sources
            $table->integer('low_stock_threshold')->default(5);
            $table->integer('critical_stock_threshold')->default(0);
            $table->boolean('notify_email')->default(true);
            $table->string('email_recipients')->nullable(); // comma-separated
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('inventory_source_id')->references('id')->on('inventory_sources')->nullOnDelete();
        });

        Schema::create('stock_alert_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rule_id')->nullable();
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('inventory_source_id')->nullable();
            $table->string('alert_type'); // low_stock, critical_stock, out_of_stock, back_in_stock
            $table->integer('current_qty');
            $table->integer('threshold');
            $table->boolean('notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->foreign('rule_id')->references('id')->on('stock_alert_rules')->nullOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();

            $table->index(['product_id', 'alert_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_alert_logs');
        Schema::dropIfExists('stock_alert_rules');
    }
};
