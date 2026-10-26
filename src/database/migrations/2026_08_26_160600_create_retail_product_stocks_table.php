<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Per-warehouse current stock, derived/synchronized from retail_stock_ledgers.
     * This is a performance cache, not the source of truth — never write to it
     * except through StockService.
     */
    public function up()
    {
        Schema::create('retail_product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('retail_products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('retail_warehouses')->cascadeOnDelete();
            $table->decimal('quantity', 15, 4)->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_product_stocks');
    }
};
