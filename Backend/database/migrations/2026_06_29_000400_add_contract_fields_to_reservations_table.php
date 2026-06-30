<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('payment_status');
            $table->json('second_driver')->nullable()->after('notes');
            $table->string('driver_license_path')->nullable()->after('second_driver');
            $table->string('identity_path')->nullable()->after('driver_license_path');
            $table->string('contract_number')->nullable()->unique()->after('reservation_number');
            $table->timestamp('contract_generated_at')->nullable()->after('contract_number');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropUnique(['contract_number']);
            $table->dropColumn([
                'payment_method',
                'second_driver',
                'driver_license_path',
                'identity_path',
                'contract_number',
                'contract_generated_at',
            ]);
        });
    }
};
