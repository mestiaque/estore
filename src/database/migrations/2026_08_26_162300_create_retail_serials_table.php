<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('retail_products')->restrictOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('retail_warehouses')->nullOnDelete();
            $table->foreignId('purchase_item_id')->nullable()->constrained('retail_purchase_items')->nullOnDelete();
            $table->foreignId('sale_item_id')->nullable()->constrained('retail_sale_items')->nullOnDelete();
            $table->string('serial_number');
            $table->string('status')->default('available'); // available | reserved | sold | returned | warranty | damaged | lost
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->unsignedInteger('warranty_months')->nullable();
            $table->date('warranty_start')->nullable();
            $table->date('warranty_end')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'serial_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_serials');
    }
};
