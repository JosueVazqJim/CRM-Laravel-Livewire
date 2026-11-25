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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_serial_id')->constrained('product_serials')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Tipos: entry | sale | damage | defect | adjustment | return
            $table->string('movement_type');

            $table->string('from_status')->nullable();
            $table->string('to_status');

            $table->text('notes')->nullable();
            $table->timestamp('moved_at')->useCurrent();

            $table->index(['product_serial_id', 'movement_type']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
