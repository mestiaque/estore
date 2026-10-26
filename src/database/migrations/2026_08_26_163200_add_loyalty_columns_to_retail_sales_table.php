<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('retail_sales', function (Blueprint $table) {
            $table->decimal('points_earned', 12, 2)->default(0)->after('tax');
            $table->decimal('points_redeemed', 12, 2)->default(0)->after('points_earned');
            $table->decimal('points_discount', 15, 2)->default(0)->after('points_redeemed');
        });

        Schema::table('retail_sale_items', function (Blueprint $table) {
            $table->decimal('points_earned', 12, 2)->default(0)->after('total');
        });
    }

    public function down()
    {
        Schema::table('retail_sales', function (Blueprint $table) {
            $table->dropColumn(['points_earned', 'points_redeemed', 'points_discount']);
        });

        Schema::table('retail_sale_items', function (Blueprint $table) {
            $table->dropColumn('points_earned');
        });
    }
};
