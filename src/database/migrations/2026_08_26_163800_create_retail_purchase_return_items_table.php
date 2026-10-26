<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained('retail_purchase_returns')->cascadeOnDelete();
            $table->foreignId('purchase_item_id')->constrained('retail_purchase_items')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('retail_products')->restrictOnDelete();
            $table->foreignId('serial_id')->nullable()->constrained('retail_serials')->nullOnDelete();
            $table->decimal('quantity', 15, 4);      // in the original purchase item's unit
            $table->decimal('quantity_base', 15, 4); // converted to base unit, removed from stock
            $table->decimal('refund_amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_purchase_return_items');
    }
};
