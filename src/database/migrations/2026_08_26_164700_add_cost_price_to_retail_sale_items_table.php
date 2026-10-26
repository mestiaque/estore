<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Cost captured at the moment of sale (from the product's cost at that time),
     * never recalculated later — profit reporting must never use today's cost
     * against a historical sale (plan §46).
     */
    public function up()
    {
        Schema::table('retail_sale_items', function (Blueprint $table) {
            $table->decimal('cost_price', 15, 2)->default(0)->after('sale_price');
        });
    }

    public function down()
    {
        Schema::table('retail_sale_items', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
