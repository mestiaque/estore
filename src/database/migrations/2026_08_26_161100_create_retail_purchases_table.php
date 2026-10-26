<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('retail_suppliers')->restrictOnDelete();
            $table->foreignId('store_id')->constrained('retail_stores')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('retail_warehouses')->restrictOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('reference_number')->nullable();
            $table->date('purchase_date');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('due_amount', 15, 2)->default(0);
            $table->string('payment_status')->default('unpaid'); // unpaid | partial | paid
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('purchase_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_purchases');
    }
};
