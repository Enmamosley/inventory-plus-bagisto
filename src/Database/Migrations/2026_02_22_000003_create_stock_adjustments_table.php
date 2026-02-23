<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->unsignedInteger('inventory_source_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('type'); // count, adjustment, write_off
            $table->string('status')->default('draft'); // draft, completed, cancelled
            $table->text('reason')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('inventory_source_id')->references('id')->on('inventory_sources')->cascadeOnDelete();
            $table->index('status');
        });

        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('adjustment_id');
            $table->unsignedInteger('product_id');
            $table->integer('qty_system'); // what the system says
            $table->integer('qty_counted'); // what was physically counted
            $table->integer('qty_difference'); // counted - system
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('adjustment_id')->references('id')->on('stock_adjustments')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_items');
        Schema::dropIfExists('stock_adjustments');
    }
};
