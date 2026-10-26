<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('retail_categories')->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('retail_brands')->nullOnDelete();
            $table->foreignId('unit_id')->constrained('retail_units')->restrictOnDelete();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('sale_price', 15, 2)->default(0);
            $table->decimal('wholesale_price', 15, 2)->nullable();
            // Current on-hand quantity in base unit; authoritative history lives in the stock ledger (Phase 4).
            $table->decimal('stock_quantity', 15, 4)->default(0);
            $table->string('track_type')->default('normal'); // normal | batch | serial
            $table->boolean('has_expiry')->default(false);
            $table->boolean('status')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_products');
    }
};
