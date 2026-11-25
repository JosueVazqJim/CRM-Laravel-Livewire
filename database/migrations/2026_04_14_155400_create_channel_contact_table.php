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
        Schema::create('channel_contact', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_contact_id')->nullable()->constrained('contact_people')->noActionOnDelete()->noActionOnUpdate();
            $table->string('type')->nullable();
            $table->string('channel')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_contact');
    }
};
