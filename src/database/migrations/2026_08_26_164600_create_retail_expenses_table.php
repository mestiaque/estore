<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('retail_stores')->restrictOnDelete();
            $table->foreignId('expense_category_id')->constrained('retail_expense_categories')->restrictOnDelete();
            $table->foreignId('cash_register_session_id')->nullable()->constrained('retail_cash_register_sessions')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->default('cash'); // cash | bank | card | mobile_banking | other
            $table->date('expense_date');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_expenses');
    }
};
