<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Configurable discount rules, replacing hard-coded percentages:
     * - product / category / brand: applies to matching line items
     * - quantity: applies once a line item's quantity reaches min_quantity
     * - coupon: applies once at invoice level when the code is entered
     */
    public function up()
    {
        Schema::create('retail_discount_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // product | category | brand | quantity | coupon
            $table->foreignId('target_id')->nullable(); // product/category/brand id depending on type — no FK, polymorphic-by-type
            $table->string('coupon_code')->nullable()->unique();
            $table->decimal('min_quantity', 15, 4)->nullable();
            $table->string('discount_type'); // percent | flat
            $table->decimal('discount_value', 15, 2);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->unsignedInteger('sl')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['type', 'target_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_discount_rules');
    }
};
