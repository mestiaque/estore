<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Append-only customer ledger. Positive amount increases what the
     * customer owes us (a credit sale); negative decreases it (a payment or
     * a sale return credit). Customer due = opening_balance + SUM(amount).
     */
    public function up()
    {
        Schema::create('retail_customer_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('retail_customers')->restrictOnDelete();
            $table->string('type'); // sale | payment | sale_return | adjustment
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->nullableMorphs('reference');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('customer_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_customer_transactions');
    }
};
