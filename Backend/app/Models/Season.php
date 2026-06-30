<?php

namespace App\Models;

use App\Enums\SeasonPriceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date_from',
        'date_to',
        'price_type',
        'price_value',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'price_value' => 'decimal:2',
        'price_type' => SeasonPriceType::class,
    ];

    public function carPrices(): HasMany
    {
        return $this->hasMany(CarSeasonPrice::class);
    }

    public function cars(): BelongsToMany
    {
        return $this->belongsToMany(Car::class, 'car_season_prices')
            ->withPivot(['id', 'price_per_day']);
    }
}
