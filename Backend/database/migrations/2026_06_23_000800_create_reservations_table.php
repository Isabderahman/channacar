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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_number')->unique();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('car_id')->constrained()->restrictOnDelete();
            $table->foreignId('pickup_location_id')
                ->constrained('pickup_locations')
                ->restrictOnDelete();
            $table->foreignId('dropoff_location_id')
                ->constrained('pickup_locations')
                ->restrictOnDelete();
            $table->date('pickup_date');
            $table->time('pickup_time');
            $table->date('dropoff_date');
            $table->time('dropoff_time');
            $table->enum('status', ['pending', 'confirmed', 'ongoing', 'completed', 'cancelled'])
                ->default('pending');
            $table->enum('payment_status', ['unpaid', 'deposit', 'paid'])->default('unpaid');
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->enum('source', ['web', 'phone', 'walkin'])->default('web');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'payment_status']);
            $table->index(['pickup_date', 'dropoff_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
