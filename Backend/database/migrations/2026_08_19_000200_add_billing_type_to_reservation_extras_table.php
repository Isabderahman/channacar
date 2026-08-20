<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservation_extras', function (Blueprint $table) {
            $table->enum('billing_type', ['per_day', 'one_time'])
                ->default('per_day')
                ->after('price_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('reservation_extras', function (Blueprint $table) {
            $table->dropColumn('billing_type');
        });
    }
};
