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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // Relaciones
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('product_type_id')->nullable()->constrained('product_types')->restrictOnDelete();
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories')->restrictOnDelete();

            // Identificación
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('image_path')->nullable();

            // Precios
            $table->char('currency', 3)->default('USD');
            $table->decimal('price_regular', 12, 2)->nullable();
            $table->decimal('price_min', 12, 2)->comment('Precio cliente')->default(0)->nullable();
            $table->decimal('price_max', 12, 2)->comment('Precio proveedor/industrial')->default(0)->nullable();
            $table->decimal('installation_cost', 12, 2)->default(0)->default(0)->nullable();

            // Stock (calculado por observer, no editar manualmente)
            $table->unsignedInteger('stock_count')->default(0);

            // Variante
            $table->string('color')->nullable();

            $table->string('slug')->nullable()->unique();

            // Descripciones
            $table->text('short_description')->nullable();
            $table->text('long_description')->nullable();

            // Estado
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
