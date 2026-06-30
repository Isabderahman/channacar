<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'content',
        'stars',
        'is_visible',
    ];

    protected $casts = [
        'stars' => 'integer',
        'is_visible' => 'boolean',
    ];
}
