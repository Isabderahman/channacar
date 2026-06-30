<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Existing driver_license_path / identity_path hold the recto (front);
            // these hold the verso (back) of each document.
            $table->string('driver_license_verso_path')->nullable()->after('driver_license_path');
            $table->string('identity_verso_path')->nullable()->after('identity_path');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['driver_license_verso_path', 'identity_verso_path']);
        });
    }
};
