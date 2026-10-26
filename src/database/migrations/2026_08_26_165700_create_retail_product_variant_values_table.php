<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Which specific attribute value a variant row carries (e.g. variant #5
     * -> Color=Red, variant #5 -> Size=M). One row per attribute per variant.
     */
    public function up()
    {
        Schema::create('retail_product_variant_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('retail_products')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained('retail_attribute_values')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'attribute_value_id'], 'retail_product_variant_values_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_product_variant_values');
    }
};
