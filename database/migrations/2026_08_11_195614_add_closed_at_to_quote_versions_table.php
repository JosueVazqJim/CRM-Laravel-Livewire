<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quote_versions', function (Blueprint $table) {
            $table->timestamp('closed_at')->nullable()->after('status');
        });

        // Backfill: para cotizaciones ya marcadas como exitosas, usamos su
        // updated_at actual como mejor aproximación disponible de la fecha de cierre.
        DB::table('quote_versions')
            ->where('status', 'exitosa')
            ->update(['closed_at' => DB::raw('updated_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_versions', function (Blueprint $table) {
            $table->dropColumn('closed_at');
        });
    }
};
