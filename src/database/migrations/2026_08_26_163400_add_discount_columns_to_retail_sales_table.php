<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('retail_sales', function (Blueprint $table) {
            // The manual `discount` column stays cashier-entered; this tracks the
            // portion the Pricing Engine added automatically (membership/level/coupon),
            // kept separate so an invoice never hides how a discount was derived.
            $table->decimal('auto_discount', 15, 2)->default(0)->after('discount');
            $table->string('coupon_code')->nullable()->after('auto_discount');
            $table->foreignId('discount_rule_id')->nullable()->after('coupon_code')->constrained('retail_discount_rules')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('retail_sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_rule_id');
            $table->dropColumn(['auto_discount', 'coupon_code']);
        });
    }
};
