<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Append-only cash movement ledger for a register session. Positive
     * amount puts cash into the drawer (a cash sale, other income); negative
     * takes it out (a refund, an expense paid from the drawer).
     */
    public function up()
    {
        Schema::create('retail_cash_register_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_session_id')->constrained('retail_cash_register_sessions', 'id', 'retail_cash_register_transactions_session_fk')->restrictOnDelete();
            $table->string('type'); // sale | refund | expense | purchase_payment | other_income | adjustment
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->nullableMorphs('reference', 'retail_cash_register_transactions_reference_idx');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('cash_register_session_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_cash_register_transactions');
    }
};
