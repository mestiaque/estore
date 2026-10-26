<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('retail_sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('retail_products')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('retail_units')->restrictOnDelete();
            $table->decimal('quantity', 15, 4);              // in the unit selected on the sale item
            $table->decimal('quantity_base', 15, 4);          // converted to the product's base unit for stock
            $table->decimal('returned_quantity_base', 15, 4)->default(0); // running total already returned
            $table->decimal('sale_price', 15, 2);             // per sale unit
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_sale_items');
    }
};
