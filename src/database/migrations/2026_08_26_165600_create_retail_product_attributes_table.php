<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Which attributes a template product varies by (e.g. T-Shirt -> Color, Size).
     */
    public function up()
    {
        Schema::create('retail_product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('retail_products')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('retail_attributes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'attribute_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_product_attributes');
    }
};
