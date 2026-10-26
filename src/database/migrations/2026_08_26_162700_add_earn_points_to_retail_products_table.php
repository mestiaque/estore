<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('retail_products', function (Blueprint $table) {
            // Loyalty points earned per base unit sold. 0 = not eligible for points.
            $table->decimal('earn_points', 10, 2)->default(0)->after('has_expiry');
        });
    }

    public function down()
    {
        Schema::table('retail_products', function (Blueprint $table) {
            $table->dropColumn('earn_points');
        });
    }
};
