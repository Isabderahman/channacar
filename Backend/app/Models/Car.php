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
        'caution',
        'tags',
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
        'caution' => 'decimal:2',
        'tags' => 'array',
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

    /** Most tags a car can carry; keeps the public chips row to one line or two. */
    public const MAX_TAGS = 8;

    /** Longest accepted tag, in characters. */
    public const MAX_TAG_LENGTH = 30;

    /**
     * Clean admin-entered tags: trim, collapse inner whitespace, drop empties
     * and case-insensitive duplicates (first spelling wins), keep the order.
     *
     * @param  array<int, mixed>|null  $tags
     * @return array<int, string>
     */
    public static function normalizeTags(?array $tags): array
    {
        $clean = [];

        foreach ($tags ?? [] as $tag) {
            $tag = trim((string) preg_replace('/\s+/u', ' ', (string) $tag));

            if ($tag === '') {
                continue;
            }

            $key = mb_strtolower($tag);
            $clean[$key] ??= $tag;
        }

        return array_values($clean);
    }

    /** Main category: shown where only one fits (contract, breadcrumb). */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Every category the car is listed under (main one included). */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->orderBy('name');
    }

    /**
     * Set the car's categories; the first id becomes the main category.
     *
     * @param  array<int, int>  $categoryIds
     */
    public function assignCategories(array $categoryIds): void
    {
        $categoryIds = array_values(array_unique(array_map('intval', $categoryIds)));

        if ($categoryIds === []) {
            return;
        }

        if ((int) $this->category_id !== $categoryIds[0]) {
            $this->forceFill(['category_id' => $categoryIds[0]])->save();
        }

        $this->categories()->sync($categoryIds);
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
