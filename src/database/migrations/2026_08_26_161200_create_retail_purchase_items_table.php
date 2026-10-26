<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('retail_purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('retail_products')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('retail_units')->restrictOnDelete();
            $table->decimal('quantity', 15, 4);               // in the unit selected on the purchase item
            $table->decimal('quantity_base', 15, 4);           // converted to the product's base unit for stock
            $table->decimal('purchase_price', 15, 2);          // per purchase unit
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_purchase_items');
    }
};
