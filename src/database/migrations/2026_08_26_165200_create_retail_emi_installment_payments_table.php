<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Append-only payment history per installment — an installment's
     * paid_amount/remaining_amount are a cache derived from these rows.
     */
    public function up()
    {
        Schema::create('retail_emi_installment_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emi_installment_id')->constrained('retail_emi_installments')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->default('cash');
            $table->foreignId('cash_register_session_id')->nullable()->constrained('retail_cash_register_sessions')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_emi_installment_payments');
    }
};
