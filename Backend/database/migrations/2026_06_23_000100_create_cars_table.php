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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('brand');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('doors');
            $table->unsignedTinyInteger('seats');
            $table->unsignedTinyInteger('luggage');
            $table->enum('transmission', ['manual', 'auto']);
            $table->enum('fuel', ['diesel', 'petrol', 'hybrid', 'electric']);
            $table->boolean('climatisation')->default(false);
            $table->boolean('gps')->default(false);
            $table->decimal('base_price_per_day', 10, 2);
            $table->enum('status', ['available', 'rented', 'maintenance'])->default('available');
            $table->string('registration_number')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['status', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
