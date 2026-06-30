<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PickupLocation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'address',
        'delivery_fee',
        'is_active',
    ];

    protected $casts = [
        'delivery_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function pickupReservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'pickup_location_id');
    }

    public function dropoffReservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'dropoff_location_id');
    }
}
