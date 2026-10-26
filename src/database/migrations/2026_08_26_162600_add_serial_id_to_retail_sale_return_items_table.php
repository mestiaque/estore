<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('retail_sale_return_items', function (Blueprint $table) {
            $table->foreignId('serial_id')->nullable()->after('sale_item_id')->constrained('retail_serials')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('retail_sale_return_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('serial_id');
        });
    }
};
