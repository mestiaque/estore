<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The item code a weighing scale prints inside its barcode (PLU) for loose
     * goods sold by weight — fish, meat, vegetables. See BarcodeLookupService.
     */
    public function up()
    {
        Schema::table('retail_products', function (Blueprint $table) {
            $table->string('plu_code', 20)->nullable()->after('barcode')->unique();
        });
    }

    public function down()
    {
        Schema::table('retail_products', function (Blueprint $table) {
            $table->dropUnique(['plu_code']);
            $table->dropColumn('plu_code');
        });
    }
};
