<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('retail_sale_items', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('unit_id')->constrained('retail_batches')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('retail_sale_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('batch_id');
        });
    }
};
