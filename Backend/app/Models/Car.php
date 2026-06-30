<?php

namespace App\Models;

use App\Enums\CarStatus;
use App\Enums\FuelType;
use App\Enums\TransmissionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'brand',
        'year',
        'category_id',
        'doors',
        'seats',
        'luggage',
        'transmission',
        'fuel',
        'climatisation',
        'gps',
        'base_price_per_day',
        'insurance_price_per_day',
        'status',
        'registration_number',
        'is_active',
    ];

    protected $casts = [
        'year' => 'integer',
        'doors' => 'integer',
        'seats' => 'integer',
        'luggage' => 'integer',
        'climatisation' => 'boolean',
        'gps' => 'boolean',
        'base_price_per_day' => 'decimal:2',
        'insurance_price_per_day' => 'decimal:2',
        'is_active' => 'boolean',
        'transmission' => TransmissionType::class,
        'fuel' => FuelType::class,
        'status' => CarStatus::class,
    ];

    protected static function booted(): void
    {
        static::saving(function (Car $car) {
            if (blank($car->slug)) {
                $car->slug = static::generateUniqueSlug($car->brand.' '.$car->name, $car->id);
            }
        });
    }

    /**
     * Build a URL-safe, unique slug from the brand + model name, appending a
     * numeric suffix when another car already owns the base slug.
     */
    protected static function generateUniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug(trim($source)) ?: 'vehicule';
        $slug = $base;
        $suffix = 2;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(CarImage::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function seasonPrices(): HasMany
    {
        return $this->hasMany(CarSeasonPrice::class);
    }

    public function seasons(): BelongsToMany
    {
        return $this->belongsToMany(Season::class, 'car_season_prices')
            ->withPivot(['id', 'price_per_day']);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
