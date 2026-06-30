<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ReservationExtra extends Pivot
{
    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'reservation_extras';

    protected $fillable = [
        'reservation_id',
        'extra_id',
        'price_snapshot',
    ];

    protected $casts = [
        'price_snapshot' => 'decimal:2',
    ];
}
