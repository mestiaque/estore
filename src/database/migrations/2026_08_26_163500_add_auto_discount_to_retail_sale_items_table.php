<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('retail_sale_items', function (Blueprint $table) {
            $table->decimal('auto_discount', 15, 2)->default(0)->after('discount');
            $table->foreignId('discount_rule_id')->nullable()->after('auto_discount')->constrained('retail_discount_rules')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('retail_sale_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_rule_id');
            $table->dropColumn('auto_discount');
        });
    }
};
