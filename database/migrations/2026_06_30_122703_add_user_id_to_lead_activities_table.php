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
        Schema::table('lead_activities', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('lead_id')
                ->constrained('users')->noActionOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('lead_activities', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
