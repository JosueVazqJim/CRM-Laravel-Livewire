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
        // Snapshot de tipos de cambio capturado en el momento de crear
        // la versión. Garantiza inmutabilidad de los cálculos.
        Schema::create('quote_exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_version_id')
                ->constrained('quote_versions')
                ->cascadeOnDelete();
            $table->string('from_currency', 3);
            $table->string('to_currency', 3);
            $table->decimal('rate', 15, 6);
            $table->timestamps();

            // No puede haber dos registros del mismo par en la misma versión
            $table->unique(
                ['quote_version_id', 'from_currency', 'to_currency'],
                'qer_version_currency_unique'  // nombre corto < 64 chars
            );
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_exchange_rates');
    }
};
