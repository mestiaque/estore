<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_cash_register_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained('retail_cash_registers')->restrictOnDelete();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->nullable();
            $table->decimal('expected_closing_balance', 15, 2)->nullable();
            $table->decimal('difference', 15, 2)->nullable();
            $table->string('status')->default('open'); // open | closed
            $table->text('notes')->nullable();
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['cash_register_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_cash_register_sessions');
    }
};
