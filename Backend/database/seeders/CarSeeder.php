<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    /**
     * Seed the fleet with the real ChanaaCar vehicles (chanaacar.com).
     *
     * Prices are stored in MAD (DH) per day; the public economy rate of ~€30/day
     * maps to ~300 DH/day. Categories are resolved by name and created on demand
     * so this seeder can run independently of CategorySeeder.
     */
    public function run(): void
    {
        $cars = [
            // --- Citadines (city cars) ---
            ['brand' => 'Renault', 'name' => 'Clio 5', 'category' => 'Citadines', 'year' => 2023, 'doors' => 5, 'seats' => 5, 'luggage' => 2, 'transmission' => 'manual', 'fuel' => 'diesel', 'gps' => false, 'price' => 300, 'plate' => '11542-A-6'],
            ['brand' => 'Peugeot', 'name' => '208', 'category' => 'Citadines', 'year' => 2023, 'doors' => 5, 'seats' => 5, 'luggage' => 2, 'transmission' => 'manual', 'fuel' => 'diesel', 'gps' => false, 'price' => 320, 'plate' => '22673-B-6'],
            ['brand' => 'Opel', 'name' => 'Corsa', 'category' => 'Citadines', 'year' => 2022, 'doors' => 5, 'seats' => 5, 'luggage' => 2, 'transmission' => 'manual', 'fuel' => 'petrol', 'gps' => false, 'price' => 300, 'plate' => '33784-A-1'],

            // --- Économique ---
            ['brand' => 'Dacia', 'name' => 'Sandero', 'category' => 'Économique', 'year' => 2023, 'doors' => 5, 'seats' => 5, 'luggage' => 2, 'transmission' => 'auto', 'fuel' => 'diesel', 'gps' => false, 'price' => 320, 'plate' => '44895-B-1'],
            ['brand' => 'Dacia', 'name' => 'Logan', 'category' => 'Économique', 'year' => 2022, 'doors' => 5, 'seats' => 5, 'luggage' => 3, 'transmission' => 'manual', 'fuel' => 'diesel', 'gps' => false, 'price' => 280, 'plate' => '55906-A-2'],
            ['brand' => 'Dacia', 'name' => 'Logan (Automatique)', 'category' => 'Économique', 'year' => 2023, 'doors' => 5, 'seats' => 5, 'luggage' => 3, 'transmission' => 'auto', 'fuel' => 'diesel', 'gps' => false, 'price' => 330, 'plate' => '66017-B-2'],
            ['brand' => 'Hyundai', 'name' => 'Accent', 'category' => 'Économique', 'year' => 2023, 'doors' => 5, 'seats' => 5, 'luggage' => 3, 'transmission' => 'auto', 'fuel' => 'diesel', 'gps' => false, 'price' => 350, 'plate' => '77128-A-3'],

            // --- Familiale ---
            ['brand' => 'Dacia', 'name' => 'Jogger 7 places', 'category' => 'Familiale', 'year' => 2023, 'doors' => 5, 'seats' => 7, 'luggage' => 4, 'transmission' => 'manual', 'fuel' => 'diesel', 'gps' => true, 'price' => 450, 'plate' => '88239-B-3'],
            ['brand' => 'Volkswagen', 'name' => 'Golf 8', 'category' => 'Familiale', 'year' => 2023, 'doors' => 5, 'seats' => 5, 'luggage' => 3, 'transmission' => 'auto', 'fuel' => 'diesel', 'gps' => true, 'price' => 500, 'plate' => '99340-A-4'],

            // --- SUV ---
            ['brand' => 'Volkswagen', 'name' => 'T-Roc', 'category' => 'SUV', 'year' => 2023, 'doors' => 5, 'seats' => 5, 'luggage' => 3, 'transmission' => 'auto', 'fuel' => 'diesel', 'gps' => true, 'price' => 650, 'plate' => '10451-B-4'],

            // --- Premium ---
            ['brand' => 'Audi', 'name' => 'Q3', 'category' => 'Premium', 'year' => 2023, 'doors' => 5, 'seats' => 5, 'luggage' => 4, 'transmission' => 'auto', 'fuel' => 'diesel', 'gps' => true, 'price' => 850, 'plate' => '11562-A-5'],
            ['brand' => 'Mercedes-Benz', 'name' => 'Classe A', 'category' => 'Premium', 'year' => 2023, 'doors' => 5, 'seats' => 5, 'luggage' => 3, 'transmission' => 'auto', 'fuel' => 'petrol', 'gps' => true, 'price' => 700, 'plate' => '12673-B-5'],
            ['brand' => 'Volkswagen', 'name' => 'Touareg', 'category' => 'Premium', 'year' => 2022, 'doors' => 5, 'seats' => 5, 'luggage' => 4, 'transmission' => 'auto', 'fuel' => 'diesel', 'gps' => true, 'price' => 1000, 'plate' => '13784-A-6'],
            ['brand' => 'Land Rover', 'name' => 'Range Rover Evoque', 'category' => 'Premium', 'year' => 2023, 'doors' => 5, 'seats' => 5, 'luggage' => 4, 'transmission' => 'auto', 'fuel' => 'diesel', 'gps' => true, 'price' => 1200, 'plate' => '14895-B-6'],
            ['brand' => 'Porsche', 'name' => 'Macan', 'category' => 'Premium', 'year' => 2023, 'doors' => 5, 'seats' => 5, 'luggage' => 4, 'transmission' => 'auto', 'fuel' => 'petrol', 'gps' => true, 'price' => 1800, 'plate' => '15906-A-1'],
        ];

        // Optional per-car insurance fee (MAD/day) by category.
        $insuranceByCategory = [
            'Citadines' => 60,
            'Économique' => 60,
            'Familiale' => 80,
            'SUV' => 120,
            'Premium' => 200,
        ];

        $categoryIds = [];

        foreach ($cars as $car) {
            $categoryName = $car['category'];

            $categoryIds[$categoryName] ??= Category::query()
                ->firstOrCreate(['name' => $categoryName])
                ->id;

            Car::query()->updateOrCreate(
                ['registration_number' => $car['plate']],
                [
                    'category_id' => $categoryIds[$categoryName],
                    'name' => $car['name'],
                    'brand' => $car['brand'],
                    'year' => $car['year'],
                    'doors' => $car['doors'],
                    'seats' => $car['seats'],
                    'luggage' => $car['luggage'],
                    'transmission' => $car['transmission'],
                    'fuel' => $car['fuel'],
                    'climatisation' => true,
                    'gps' => $car['gps'],
                    'base_price_per_day' => $car['price'],
                    'insurance_price_per_day' => $insuranceByCategory[$categoryName] ?? 80,
                    'status' => 'available',
                    'is_active' => true,
                ],
            );
        }
    }
}
