<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('retail_stores')->restrictOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_cash_registers');
    }
};
