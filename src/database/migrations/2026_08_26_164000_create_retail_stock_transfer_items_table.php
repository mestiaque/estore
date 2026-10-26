<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('retail_stock_transfers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('retail_products')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('retail_units')->restrictOnDelete();
            $table->foreignId('source_batch_id')->nullable()->constrained('retail_batches')->nullOnDelete();
            $table->foreignId('destination_batch_id')->nullable()->constrained('retail_batches')->nullOnDelete();
            $table->decimal('quantity', 15, 4);
            $table->decimal('quantity_base', 15, 4);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_stock_transfer_items');
    }
};
