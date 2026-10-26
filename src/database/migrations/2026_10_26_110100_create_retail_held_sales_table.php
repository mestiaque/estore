<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * A POS cart parked mid-sale so the cashier can serve the next customer and
     * resume it later. Only the unsaved form is stored — no stock, ledger or
     * payment is touched until the resumed sale is completed.
     */
    public function up()
    {
        Schema::create('retail_held_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('retail_customers')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('retail_warehouses')->nullOnDelete();
            $table->string('reference')->nullable();
            $table->json('payload');
            $table->unsignedInteger('item_count')->default(0);
            $table->decimal('estimated_total', 15, 2)->default(0);
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_held_sales');
    }
};
