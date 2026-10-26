<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_customer_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // A customer qualifies for a level once their lifetime earned points reach this threshold.
            $table->decimal('min_points', 12, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('point_multiplier', 5, 2)->default(1);
            $table->unsignedInteger('sl')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_customer_levels');
    }
};
