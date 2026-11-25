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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('base_currency')->default('USD');

            // usado para informar que a partir de otra cotizacion se realizo esta nueva cotizacion (recotizacion)
            $table->foreignId('parent_quote_id')
                ->nullable()
                ->constrained('quotes')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
