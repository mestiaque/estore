<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('retail_products')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('retail_warehouses')->restrictOnDelete();
            $table->string('batch_number');
            $table->date('expiry_date')->nullable();
            $table->decimal('purchase_price', 15, 2)->default(0);
            // Current on-hand quantity in this batch, at this warehouse, in the product's base unit.
            $table->decimal('quantity', 15, 4)->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id', 'batch_number']);
            $table->index('expiry_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_batches');
    }
};
