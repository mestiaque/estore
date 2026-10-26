<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Append-only supplier ledger. Positive amount increases what we owe the
     * supplier (e.g. a purchase on credit); negative decreases it (a payment
     * or a purchase return credit). Supplier due = SUM(amount).
     */
    public function up()
    {
        Schema::create('retail_supplier_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('retail_suppliers')->restrictOnDelete();
            $table->string('type'); // purchase | payment | purchase_return | adjustment
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->nullableMorphs('reference');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('supplier_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_supplier_transactions');
    }
};
