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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->noActionOnDelete()->noActionOnUpdate();
            $table->foreignId('user_id')->nullable()->constrained('users')->noActionOnDelete()->noActionOnUpdate();
            $table->foreignId('billing_id')->nullable()->constrained('billing_infos')->noActionOnDelete()->noActionOnUpdate();
            $table->string('name_company')->nullable();
            $table->string('country_state')->nullable();
            $table->string('urgency_level')->nullable();
            $table->integer('budget')->nullable();
            $table->string('undermine_channel')->nullable();
            $table->string('classification')->nullable();
            $table->string('funnel_stage')->nullable();
            $table->string('client_progress')->nullable();
            $table->string('pay_status')->nullable();
            $table->string('after_sales')->nullable();
            $table->longText('problem_detected')->nullable();
            $table->boolean('demo_offered')->nullable();
            $table->integer('probability')->nullable();
            $table->integer('room_number')->nullable();
            $table->string('client_status')->nullable();
            $table->integer('center_number')->nullable();
            $table->boolean('first_contact')->nullable();
            $table->boolean('differentiator_explained')->nullable();
            $table->boolean('diagnosis_made')->nullable();
            $table->dateTime('last_contact')->nullable();
            $table->datetime('creat_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
