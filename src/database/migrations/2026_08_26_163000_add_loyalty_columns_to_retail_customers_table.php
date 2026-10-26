<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('retail_customers', function (Blueprint $table) {
            $table->foreignId('customer_level_id')->nullable()->after('credit_limit')->constrained('retail_customer_levels')->nullOnDelete();
            $table->foreignId('membership_id')->nullable()->after('customer_level_id')->constrained('retail_memberships')->nullOnDelete();
            $table->date('membership_expires_at')->nullable()->after('membership_id');
        });
    }

    public function down()
    {
        Schema::table('retail_customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_level_id');
            $table->dropConstrainedForeignId('membership_id');
            $table->dropColumn('membership_expires_at');
        });
    }
};
