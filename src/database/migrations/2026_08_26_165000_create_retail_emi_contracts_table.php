<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_emi_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('retail_sales')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('retail_customers')->restrictOnDelete();
            $table->foreignId('emi_bank_id')->constrained('retail_emi_banks')->restrictOnDelete();
            $table->foreignId('emi_plan_id')->constrained('retail_emi_plans')->restrictOnDelete();
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_amount', 15, 2);
            $table->decimal('fee_amount', 15, 2);
            $table->decimal('total_payable', 15, 2);
            $table->decimal('down_payment', 15, 2)->default(0);
            $table->decimal('financed_amount', 15, 2); // total_payable - down_payment, spread across installments
            $table->unsignedInteger('installment_count');
            $table->date('start_date');
            $table->string('status')->default('active'); // active | completed | cancelled
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_emi_contracts');
    }
};
