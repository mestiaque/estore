<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_emi_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emi_contract_id')->constrained('retail_emi_contracts')->cascadeOnDelete();
            $table->unsignedInteger('installment_number');
            $table->date('due_date');
            $table->decimal('amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2);
            $table->decimal('late_fee', 15, 2)->default(0);
            $table->date('paid_date')->nullable();
            $table->string('status')->default('pending'); // pending | partial | paid | overdue | cancelled
            $table->timestamps();

            $table->index(['emi_contract_id', 'due_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_emi_installments');
    }
};
