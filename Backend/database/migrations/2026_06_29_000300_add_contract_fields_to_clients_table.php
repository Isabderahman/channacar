<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('full_name');
            $table->string('birth_place')->nullable()->after('birth_date');
            $table->string('address')->nullable()->after('birth_place');
            $table->date('license_issued_at')->nullable()->after('driver_license');
            $table->string('license_issued_place')->nullable()->after('license_issued_at');
            $table->string('passport_number')->nullable()->after('license_issued_place');
            $table->string('cin_number')->nullable()->after('passport_number');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'birth_date',
                'birth_place',
                'address',
                'license_issued_at',
                'license_issued_place',
                'passport_number',
                'cin_number',
            ]);
        });
    }
};
