<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('state_id')
                ->nullable()
                ->after('country_state')
                ->constrained('states')
                ->noActionOnDelete()
                ->noActionOnUpdate();

            $table->dropColumn('country_state');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropColumn('state_id');
            $table->string('country_state')->nullable();
        });
    }
};
