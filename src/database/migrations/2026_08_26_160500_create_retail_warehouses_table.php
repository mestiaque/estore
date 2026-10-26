<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('retail_stores')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('address')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_warehouses');
    }
};
