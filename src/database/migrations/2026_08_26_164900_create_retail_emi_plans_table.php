<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_emi_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emi_bank_id')->constrained('retail_emi_banks')->restrictOnDelete();
            $table->string('name');
            $table->unsignedInteger('duration_months');
            $table->string('interest_type'); // flat | monthly_flat
            $table->decimal('interest_rate', 5, 2)->default(0); // percent
            $table->string('fee_type')->default('flat'); // flat | percentage
            $table->decimal('fee_value', 15, 2)->default(0);
            $table->decimal('min_amount', 15, 2)->default(0);
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_emi_plans');
    }
};
