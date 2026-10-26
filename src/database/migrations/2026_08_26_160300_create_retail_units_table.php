<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('retail_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('base_unit_id')->nullable()->constrained('retail_units')->nullOnDelete();
            $table->string('name');
            $table->string('short_name');
            // How many of this unit make up one base unit, e.g. 1 Carton = 24 (base unit Piece).
            $table->decimal('conversion_factor', 15, 4)->default(1);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retail_units');
    }
};
