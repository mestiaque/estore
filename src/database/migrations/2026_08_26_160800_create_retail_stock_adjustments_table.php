<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('retail_products')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('retail_warehouses')->restrictOnDelete();
            $table->foreignId('stock_ledger_id')->constrained('retail_stock_ledgers')->restrictOnDelete();
            $table->decimal('quantity_before', 15, 4);
            $table->decimal('quantity_adjusted', 15, 4); // signed
            $table->decimal('quantity_after', 15, 4);
            $table->string('reason'); // damage, lost, expired, physical_count, correction, opening_balance
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_stock_adjustments');
    }
};
