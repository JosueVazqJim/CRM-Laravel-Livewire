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
        Schema::create('product_serials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('Usuario que registró la entrada');

            // Identificación de la unidad física
            $table->string('serial_number')->unique();

            // Estado del ciclo de vida
            // Valores: in_stock | sold | damaged | defective
            $table->string('status')->default('in_stock');

            // Marcado visual en la tabla de seriales (hex color, ej: #FF0000)
            $table->string('flag_color', 7)->nullable();

            // Precio al que la empresa adquirió esta unidad (RF06 comentario FJ3)
            $table->decimal('purchase_price', 12, 2)->nullable();

            // Fechas del ciclo de vida (RF06 comentarios FJ1, FJ2)
            $table->date('purchased_at')->nullable()->comment('Fecha de compra por la empresa');
            $table->date('entered_stock_at')->nullable()->comment('Fecha de ingreso al stock');
            $table->date('sold_at')->nullable()->comment('Fecha en que salió en venta');
            $table->date('exited_at')->nullable()->comment('Fecha de egreso (daño, defecto, etc.)');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['product_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_serials');
    }
};
