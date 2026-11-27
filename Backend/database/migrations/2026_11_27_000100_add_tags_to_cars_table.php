<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Free-form labels the admin puts on a car ("Nouveau", "Promo", "Familiale"),
     * shown as chips on the public fleet list and the car page.
     */
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->json('tags')->nullable()->after('caution');
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
    }
};
