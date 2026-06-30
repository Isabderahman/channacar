<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'birth_date',
        'birth_place',
        'address',
        'phone',
        'whatsapp',
        'email',
        'driver_license',
        'license_issued_at',
        'license_issued_place',
        'passport_number',
        'cin_number',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'license_issued_at' => 'date',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
