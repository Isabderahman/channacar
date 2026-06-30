<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarImage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'car_id',
        'path',
        'is_thumbnail',
        'sort_order',
    ];

    protected $casts = [
        'is_thumbnail' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
