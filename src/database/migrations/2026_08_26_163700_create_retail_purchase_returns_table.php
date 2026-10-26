<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('retail_purchases')->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained('retail_suppliers')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('retail_warehouses')->restrictOnDelete();
            $table->date('return_date');
            $table->decimal('total_refund', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_purchase_returns');
    }
};
