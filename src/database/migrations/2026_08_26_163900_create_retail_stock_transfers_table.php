<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_warehouse_id')->constrained('retail_warehouses')->restrictOnDelete();
            $table->foreignId('to_warehouse_id')->constrained('retail_warehouses')->restrictOnDelete();
            $table->string('transfer_number')->unique();
            $table->date('transfer_date');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_stock_transfers');
    }
};
