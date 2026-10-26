<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('retail_sale_payments', function (Blueprint $table) {
            $table->foreignId('cash_register_session_id')->nullable()->after('payment_method')->constrained('retail_cash_register_sessions')->nullOnDelete();
        });

        Schema::table('retail_purchase_payments', function (Blueprint $table) {
            $table->foreignId('cash_register_session_id')->nullable()->after('payment_method')->constrained('retail_cash_register_sessions')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('retail_sale_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_register_session_id');
        });

        Schema::table('retail_purchase_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_register_session_id');
        });
    }
};
