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
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('type')->default('Casa');

            $table->foreignId('state')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('municipality')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('colony');
            $table->string('street');

            $table->string('exterior_number');
            $table->string('interior_number')->nullable();

            $table->string('postal_code');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
