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
        Schema::create('quote_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_version_id')->unique()->constrained('quote_versions')->cascadeOnDelete();

            $table->decimal('advance_amount', 15, 2)->nullable(); // Monto
            $table->date('advance_paid_at')->nullable(); // Fecha de anticipo
            $table->string('payment_method')->nullable(); // Medio de pago
            $table->string('reference')->nullable(); // Referencia
            $table->date('settlement_date')->nullable(); // Fecha de liquidación

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_purchase_orders');
    }
};
