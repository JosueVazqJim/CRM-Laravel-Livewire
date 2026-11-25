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
        Schema::table('product_serials', function (Blueprint $table) {
            $table->dateTime('sold_at')->nullable()->comment('Fecha y hora en que salió en venta')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_serials', function (Blueprint $table) {
            $table->date('sold_at')->nullable()->comment('Fecha en que salió en venta')->change();
        });
    }
};
