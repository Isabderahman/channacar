<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A car can belong to several categories (e.g. a Porsche Macan is both "SUV"
 * and "Premium"). `cars.category_id` stays as the car's main category — the
 * one shown where only one fits (contract, breadcrumb) — and this pivot holds
 * every category the car is listed under, main one included.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_category', function (Blueprint $table) {
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->primary(['car_id', 'category_id']);
        });

        // Every existing car keeps its current category.
        DB::table('car_category')->insertUsing(
            ['car_id', 'category_id'],
            DB::table('cars')->select('id', 'category_id')->whereNotNull('category_id'),
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('car_category');
    }
};
