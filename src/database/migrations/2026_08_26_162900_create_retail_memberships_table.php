<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_memberships', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('fee', 12, 2)->default(0);
            $table->unsignedInteger('duration_days')->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('point_multiplier', 5, 2)->default(1);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_memberships');
    }
};
