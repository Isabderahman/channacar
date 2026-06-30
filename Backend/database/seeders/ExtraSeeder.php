<?php

namespace Database\Seeders;

use App\Models\Extra;
use Illuminate\Database\Seeder;

class ExtraSeeder extends Seeder
{
    /**
     * Seed the optional reservation extras. Prices are in MAD (DH) per day;
     * the public ~5€/day maps to ~50 DH/day. Icons map to BaseIcon names on the
     * frontend so each extra renders with its own pictogram.
     */
    public function run(): void
    {
        $extras = [
            ['name' => 'Conducteur Supplémentaire', 'icon' => 'people', 'price_per_day' => 50],
            ['name' => 'Protection Accidents', 'icon' => 'shield', 'price_per_day' => 50],
            ['name' => 'Siège Enfant', 'icon' => 'baby', 'price_per_day' => 50],
            ['name' => 'Réfrigérateur Portable', 'icon' => 'snowflake', 'price_per_day' => 50],
            ['name' => 'Câble chargeur', 'icon' => 'phone', 'price_per_day' => 50],
            ['name' => 'Wifi', 'icon' => 'wifi', 'price_per_day' => 50],
            ['name' => 'Assistance', 'icon' => 'people', 'price_per_day' => 50],
            ['name' => 'Assistance dépannage étendue', 'icon' => 'car', 'price_per_day' => 50],
            ['name' => 'Option plein carburant', 'icon' => 'fuel', 'price_per_day' => 50],
        ];

        foreach ($extras as $extra) {
            Extra::query()->updateOrCreate(
                ['name' => $extra['name']],
                [
                    'icon' => $extra['icon'],
                    'price_per_day' => $extra['price_per_day'],
                    'is_active' => true,
                ],
            );
        }
    }
}
