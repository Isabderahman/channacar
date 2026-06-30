<?php

namespace Database\Seeders;

use App\Models\PickupLocation;
use Illuminate\Database\Seeder;

class PickupLocationSeeder extends Seeder
{
    /**
     * Pickup / dropoff points. Marrakech is the base (no delivery fee); other
     * cities carry a one-off delivery fee (MAD) added to the reservation when
     * used as a pickup or dropoff location.
     */
    public function run(): void
    {
        $locations = [
            ['name' => 'Aéroport Marrakech Menara', 'address' => 'Aéroport Marrakech Menara', 'delivery_fee' => 0],
            ['name' => 'Agence Guéliz', 'address' => 'Guéliz, Marrakech', 'delivery_fee' => 0],
            ['name' => 'Médina', 'address' => 'Médina, Marrakech', 'delivery_fee' => 0],
            ['name' => 'Gare ONCF Marrakech', 'address' => 'Gare Marrakech', 'delivery_fee' => 0],
            ['name' => 'Aéroport Casablanca Mohammed V', 'address' => 'Nouaceur, Casablanca', 'delivery_fee' => 600],
            ['name' => 'Casablanca centre', 'address' => 'Casablanca', 'delivery_fee' => 500],
            ['name' => 'Aéroport Agadir Al Massira', 'address' => 'Agadir', 'delivery_fee' => 500],
            ['name' => 'Agadir centre', 'address' => 'Agadir', 'delivery_fee' => 450],
            ['name' => 'Essaouira', 'address' => 'Essaouira', 'delivery_fee' => 350],
            ['name' => 'Rabat', 'address' => 'Rabat', 'delivery_fee' => 600],
            ['name' => 'Ouarzazate', 'address' => 'Ouarzazate', 'delivery_fee' => 500],
        ];

        foreach ($locations as $location) {
            PickupLocation::query()->updateOrCreate(
                ['name' => $location['name']],
                [
                    'address' => $location['address'],
                    'delivery_fee' => $location['delivery_fee'],
                    'is_active' => true,
                ],
            );
        }
    }
}
