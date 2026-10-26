<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Append-only, authoritative history of every stock movement. Never
     * updated or deleted by the application — only inserted via StockService.
     */
    public function up()
    {
        Schema::create('retail_stock_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('retail_products')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('retail_warehouses')->restrictOnDelete();
            $table->string('movement_type'); // opening_stock, purchase, purchase_return, sale, sale_return,
                                              // adjustment, damage, loss, transfer_in, transfer_out, manual_correction
            // Signed: positive increases stock, negative decreases it. Always expressed in the product's base unit.
            $table->decimal('quantity', 15, 4);
            $table->decimal('quantity_before', 15, 4);
            $table->decimal('quantity_after', 15, 4);
            $table->nullableMorphs('reference'); // reference_type / reference_id, e.g. Purchase, Sale, StockAdjustment
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['product_id', 'warehouse_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_stock_ledgers');
    }
};
