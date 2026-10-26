<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_return_id')->constrained('retail_sale_returns')->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained('retail_sale_items')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('retail_products')->restrictOnDelete();
            $table->decimal('quantity', 15, 4);       // in the original sale item's unit
            $table->decimal('quantity_base', 15, 4);  // converted to base unit, restocked
            $table->decimal('refund_amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_sale_return_items');
    }
};
