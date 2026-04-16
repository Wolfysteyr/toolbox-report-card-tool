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
        Schema::table('energy_local', function (Blueprint $table) {
            Schema::table('energy_local', function (Blueprint $table) {
                $table->string('motora_tilpums')->nullable()->change();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('energy_local', function (Blueprint $table) {
            //
        });
    }
};
