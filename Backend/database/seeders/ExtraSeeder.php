<?php

namespace Database\Seeders;

use App\Enums\ExtraBillingType;
use App\Models\Extra;
use Illuminate\Database\Seeder;

class ExtraSeeder extends Seeder
{
    /**
     * Seed the optional reservation extras. Prices are in MAD (DH); the public
     * ~5€/day maps to ~50 DH/day. `billing_type` decides whether the price is
     * charged per rental day or once for the whole booking. Icons map to
     * BaseIcon names on the frontend so each extra renders with its own
     * pictogram.
     */
    public function run(): void
    {
        $extras = [
            ['name' => 'Conducteur Supplémentaire', 'icon' => 'people', 'price_per_day' => 50, 'billing_type' => ExtraBillingType::PerDay],
            ['name' => 'Protection Accidents', 'icon' => 'shield', 'price_per_day' => 50, 'billing_type' => ExtraBillingType::PerDay],
            ['name' => 'Siège Enfant', 'icon' => 'baby', 'price_per_day' => 50, 'billing_type' => ExtraBillingType::PerDay],
            ['name' => 'Réfrigérateur Portable', 'icon' => 'snowflake', 'price_per_day' => 50, 'billing_type' => ExtraBillingType::PerDay],
            ['name' => 'Câble chargeur', 'icon' => 'phone', 'price_per_day' => 50, 'billing_type' => ExtraBillingType::OneTime],
            ['name' => 'Wifi', 'icon' => 'wifi', 'price_per_day' => 50, 'billing_type' => ExtraBillingType::PerDay],
            ['name' => 'Assistance', 'icon' => 'people', 'price_per_day' => 50, 'billing_type' => ExtraBillingType::PerDay],
            ['name' => 'Assistance dépannage étendue', 'icon' => 'car', 'price_per_day' => 50, 'billing_type' => ExtraBillingType::PerDay],
            ['name' => 'Option plein carburant', 'icon' => 'fuel', 'price_per_day' => 50, 'billing_type' => ExtraBillingType::OneTime],
        ];

        foreach ($extras as $extra) {
            Extra::query()->updateOrCreate(
                ['name' => $extra['name']],
                [
                    'icon' => $extra['icon'],
                    'price_per_day' => $extra['price_per_day'],
                    'billing_type' => $extra['billing_type'],
                    'is_active' => true,
                ],
            );
        }
    }
}
