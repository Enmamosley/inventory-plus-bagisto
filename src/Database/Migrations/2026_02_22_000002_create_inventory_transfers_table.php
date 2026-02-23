<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->unsignedInteger('source_id'); // from inventory_source
            $table->unsignedInteger('destination_id'); // to inventory_source
            $table->unsignedInteger('user_id')->nullable();

            $table->string('status')->default('pending'); // pending, in_transit, completed, cancelled
            $table->text('notes')->nullable();

            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->foreign('source_id')->references('id')->on('inventory_sources')->cascadeOnDelete();
            $table->foreign('destination_id')->references('id')->on('inventory_sources')->cascadeOnDelete();

            $table->index('status');
        });

        Schema::create('inventory_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transfer_id');
            $table->unsignedInteger('product_id');
            $table->integer('qty_requested');
            $table->integer('qty_shipped')->default(0);
            $table->integer('qty_received')->default(0);
            $table->timestamps();

            $table->foreign('transfer_id')->references('id')->on('inventory_transfers')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transfer_items');
        Schema::dropIfExists('inventory_transfers');
    }
};
