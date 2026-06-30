<?php

namespace App\Models;

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
        'is_active',
    ];

    protected $casts = [
        'price_per_day' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class, 'reservation_extras')
            ->using(ReservationExtra::class)
            ->withPivot('price_snapshot');
    }
}
