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
        Schema::create('lead_user_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->noActionOnDelete()->noActionOnUpdate();
            $table->foreignId('user_id')->constrained('users')->noActionOnDelete()->noActionOnUpdate();
            $table->enum('permission_level', ['view', 'edit', 'manage'])->default('view');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_user_access');
    }
};
