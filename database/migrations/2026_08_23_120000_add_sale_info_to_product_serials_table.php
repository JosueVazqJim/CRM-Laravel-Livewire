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
            $table->foreignId('sold_by_user_id')->nullable()->after('user_id')
                ->constrained('users')->nullOnDelete()
                ->comment('Usuario que vendió el serial');

            $table->foreignId('quote_version_id')->nullable()->after('sold_by_user_id')
                ->constrained('quote_versions')->nullOnDelete()
                ->comment('Cotización con la que salió el serial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_serials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sold_by_user_id');
            $table->dropConstrainedForeignId('quote_version_id');
        });
    }
};
