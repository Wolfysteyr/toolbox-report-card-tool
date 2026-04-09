<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('energy_local', function (Blueprint $table) {
            $table->id();
            $table->date('prev_date');
            $table->string('periods');
            $table->float('volume');
            $table->float('prev_volume');
            $table->float('summa');
            $table->float('mileage');
            $table->float('prev_mileage');
            $table->float('mileage_consumption');
            $table->string('product');
            $table->string('carno');
            $table->string('driver');
            $table->float('bakas_tilpums');
            $table->float('paterins');
            $table->float('motora_tilpums');
            $table->string('automarka');
            $table->string('atbildigais');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_local');
    }
};
