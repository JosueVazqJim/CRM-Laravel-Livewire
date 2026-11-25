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
        Schema::create('quote_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quotes')->cascadeOnDelete();

            // Letra de versión: A, B, C... (nueva versión de la misma cotización)
            $table->string('version_letter');

            // Folio: formato DD+FECHA+LETRA_VERSION+ID_COTIZACION
            $table->string('folio')->unique();

            $table->decimal('subtotal', 15, 4)->default(0);
            $table->decimal('tax', 15, 4)->default(0);
            $table->decimal('shipping_price', 15, 4)->default(0);
            $table->decimal('total', 15, 4)->default(0);

            // en_proceso | en_validacion | exitosa | sin_exito | cancelada | papelera
            $table->string('status', 20)->default('en_proceso');

            // usado para referenciar la versión original en caso de recotizaciones
            $table->foreignId('source_version_id')
                ->nullable()
                ->constrained('quote_versions')
                ->nullOnDelete();

            $table->timestamps();

            // Una cotización no puede tener dos versiones con la misma letra
            $table->unique(['quote_id', 'version_letter']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_versions');
    }
};
