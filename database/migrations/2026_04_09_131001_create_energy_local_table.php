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
            $table->date('prev_date')->nullable();
            $table->string('periods')->nullable();
            $table->float('volume')->nullable();
            $table->float('prev_volume')->nullable();
            $table->float('summa')->nullable();
            $table->float('mileage')->nullable();
            $table->float('prev_mileage')->nullable();
            $table->float('mileage_consumption')->nullable();
            $table->string('product')->nullable();
            $table->string('carno')->nullable();
            $table->string('driver')->nullable();
            $table->float('bakas_tilpums')->nullable();
            $table->float('paterins')->nullable();
            $table->float('motora_tilpums')->nullable();
            $table->string('automarka')->nullable();
            $table->string('atbildigais')->nullable();
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
