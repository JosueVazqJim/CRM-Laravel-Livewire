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
        Schema::create('contact_people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->noActionOnDelete()->noActionOnUpdate();
            $table->string('name_contact')->nullable();
            $table->boolean('recurring_client');
            $table->datetime('creat_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_people');
    }
};
