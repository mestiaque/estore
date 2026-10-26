<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('retail_purchases')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('retail_suppliers')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->default('cash'); // cash | bank | card | mobile_banking | other
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_purchase_payments');
    }
};
