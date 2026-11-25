<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_concepts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_version_id')->constrained('quote_versions')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('unit_price', 10, 4);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('discount', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_concepts');
    }
};
