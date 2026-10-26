<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * A variant is a real product row (plan §8: SKU, barcode, price, stock,
     * image, attributes — each variant needs its own), linked back to the
     * template product it was generated from. Because a variant is just a
     * Product, it automatically works with every existing Purchase/Sale/
     * Stock/Batch/Serial service without any changes to that pipeline.
     */
    public function up()
    {
        Schema::table('retail_products', function (Blueprint $table) {
            $table->foreignId('parent_product_id')->nullable()->after('id')->constrained('retail_products')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('retail_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_product_id');
        });
    }
};
