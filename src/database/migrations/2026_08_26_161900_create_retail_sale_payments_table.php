<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * A single sale may hold several rows here (cash + card + mobile banking, etc.)
     * — this is what makes mixed payment native rather than bolted on.
     */
    public function up()
    {
        Schema::create('retail_sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('retail_sales')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('retail_customers')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method'); // cash | bank | card | mobile_banking | other
            $table->string('reference')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_sale_payments');
    }
};
