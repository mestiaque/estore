<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('retail_purchase_items', function (Blueprint $table) {
            $table->decimal('returned_quantity_base', 15, 4)->default(0)->after('quantity_base');
        });
    }

    public function down()
    {
        Schema::table('retail_purchase_items', function (Blueprint $table) {
            $table->dropColumn('returned_quantity_base');
        });
    }
};
