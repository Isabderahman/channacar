<?php

namespace App\Models;

use App\Enums\ExtraBillingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Extra extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'icon',
        'price_per_day',
        'billing_type',
        'is_active',
    ];

    protected $casts = [
        'price_per_day' => 'decimal:2',
        'billing_type' => ExtraBillingType::class,
        'is_active' => 'boolean',
    ];

    public function isBilledPerDay(): bool
    {
        return $this->billing_type !== ExtraBillingType::OneTime;
    }

    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class, 'reservation_extras')
            ->using(ReservationExtra::class)
            ->withPivot('price_snapshot', 'billing_type');
    }
}
