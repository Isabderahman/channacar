<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\ReservationSource;
use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_number',
        'client_id',
        'car_id',
        'pickup_location_id',
        'dropoff_location_id',
        'pickup_date',
        'pickup_time',
        'dropoff_date',
        'dropoff_time',
        'status',
        'payment_status',
        'payment_method',
        'contract_number',
        'contract_generated_at',
        'second_driver',
        'contract_details',
        'driver_license_path',
        'driver_license_verso_path',
        'identity_path',
        'identity_verso_path',
        'deposit_amount',
        'total_price',
        'insurance_total',
        'delivery_total',
        'source',
        'notes',
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'dropoff_date' => 'date',
        'second_driver' => 'array',
        'contract_details' => 'array',
        'contract_generated_at' => 'datetime',
        'deposit_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'insurance_total' => 'decimal:2',
        'delivery_total' => 'decimal:2',
        'status' => ReservationStatus::class,
        'payment_status' => PaymentStatus::class,
        'source' => ReservationSource::class,
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function pickupLocation(): BelongsTo
    {
        return $this->belongsTo(PickupLocation::class, 'pickup_location_id');
    }

    public function dropoffLocation(): BelongsTo
    {
        return $this->belongsTo(PickupLocation::class, 'dropoff_location_id');
    }

    public function extras(): BelongsToMany
    {
        return $this->belongsToMany(Extra::class, 'reservation_extras')
            ->using(ReservationExtra::class)
            ->withPivot('price_snapshot');
    }
}
