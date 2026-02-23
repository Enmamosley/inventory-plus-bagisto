<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('inventory_source_id')->nullable();
            $table->unsignedInteger('order_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();

            $table->string('type'); // receipt, sale, adjustment, transfer_in, transfer_out, return, initial
            $table->string('reference_type')->nullable(); // order, shipment, adjustment, transfer, import
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->integer('qty_before');
            $table->integer('qty_change'); // positive = in, negative = out
            $table->integer('qty_after');

            $table->string('reason')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('inventory_source_id')->references('id')->on('inventory_sources')->nullOnDelete();

            $table->index(['product_id', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index('reference_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
