<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        // Backfill a unique slug for every existing car (brand + model name).
        $used = [];

        foreach (DB::table('cars')->select('id', 'brand', 'name')->get() as $car) {
            $base = Str::slug(trim($car->brand.' '.$car->name)) ?: 'vehicule';
            $slug = $base;
            $suffix = 2;

            while (in_array($slug, $used, true) || DB::table('cars')->where('slug', $slug)->exists()) {
                $slug = $base.'-'.$suffix;
                $suffix++;
            }

            $used[] = $slug;
            DB::table('cars')->where('id', $car->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
