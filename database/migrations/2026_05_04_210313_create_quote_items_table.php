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
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_version_id')
                ->constrained('quote_versions')
                ->cascadeOnDelete();
            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->decimal('quantity', 10, 2);

            // Precio tal como está registrado en el catálogo (moneda original del producto)
            $table->decimal('unit_price_original', 15, 4);
            $table->string('currency_original');

            // Precio convertido a la moneda base de la cotización
            $table->decimal('unit_price_converted', 15, 4);

            // Tasa utilizada para la conversión (copiada de quote_exchange_rates)
            $table->decimal('exchange_rate_used', 15, 6);

            // Precio convertido a la moneda base de la cotización y editado en caso de
            $table->decimal('unit_price_converted_custom', 15, 4);

            // true = el usuario sobreescribió el precio en lugar de usar el del catálogo
            $table->boolean('custom_price')->default(false);

            $table->decimal('discount', 15, 4)->default(0);

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
