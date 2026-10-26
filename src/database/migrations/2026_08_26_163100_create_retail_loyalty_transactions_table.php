<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Append-only points ledger. Positive points increase the customer's
     * balance (earn); negative decreases it (redeem, expiry, reversal).
     * Customer point balance = SUM(points).
     */
    public function up()
    {
        Schema::create('retail_loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('retail_customers')->restrictOnDelete();
            $table->string('type'); // earn | redeem | adjustment | expired | reversal
            $table->decimal('points', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->nullableMorphs('reference');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('customer_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_loyalty_transactions');
    }
};
