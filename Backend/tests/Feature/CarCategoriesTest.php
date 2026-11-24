<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * A car can be listed under several categories (e.g. "SUV" and "Premium");
 * the first one is its main category (`cars.category_id`).
 */
class CarCategoriesTest extends TestCase
{
    use RefreshDatabase;

    private Category $premium;

    private Category $suv;

    private Category $citadine;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create());

        $this->premium = Category::query()->create(['name' => 'Premium']);
        $this->suv = Category::query()->create(['name' => 'SUV']);
        $this->citadine = Category::query()->create(['name' => 'Citadines']);
    }

    public function test_admin_creates_a_car_in_several_categories(): void
    {
        $response = $this->postJson('/api/cars', $this->payload([
            'category_ids' => [$this->premium->id, $this->suv->id],
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.category_id', $this->premium->id)
            ->assertJsonCount(2, 'data.categories');
    }

    public function test_public_fleet_filter_matches_any_of_the_car_categories(): void
    {
        $carId = $this->postJson('/api/cars', $this->payload([
            'category_ids' => [$this->premium->id, $this->suv->id],
        ]))->json('data.id');

        foreach ([$this->premium, $this->suv] as $category) {
            $this->getJson('/api/public/cars?category_id='.$category->id)
                ->assertOk()
                ->assertJsonPath('data.0.id', $carId);
        }

        $this->getJson('/api/public/cars?category_id='.$this->citadine->id)
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_updating_categories_replaces_them_and_moves_the_main_category(): void
    {
        $carId = $this->postJson('/api/cars', $this->payload([
            'category_ids' => [$this->premium->id, $this->suv->id],
        ]))->json('data.id');

        $this->putJson("/api/cars/{$carId}", ['category_ids' => [$this->suv->id]])
            ->assertOk()
            ->assertJsonPath('data.category_id', $this->suv->id)
            ->assertJsonCount(1, 'data.categories');

        $this->assertSame([$this->suv->id], Car::query()->find($carId)->categories->pluck('id')->all());
    }

    public function test_single_category_id_is_still_accepted(): void
    {
        $response = $this->postJson('/api/cars', $this->payload(['category_id' => $this->citadine->id]));

        $response->assertCreated()
            ->assertJsonPath('data.category_id', $this->citadine->id)
            ->assertJsonPath('data.categories.0.id', $this->citadine->id);
    }

    public function test_a_car_needs_at_least_one_category(): void
    {
        $this->postJson('/api/cars', $this->payload(['category_ids' => []]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category_ids');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Macan',
            'brand' => 'Porsche',
            'year' => 2024,
            'doors' => 5,
            'seats' => 5,
            'luggage' => 3,
            'transmission' => 'auto',
            'fuel' => 'petrol',
            'climatisation' => true,
            'gps' => true,
            'base_price_per_day' => 1800,
            'status' => 'available',
            'registration_number' => 'TEST-'.uniqid(),
            'is_active' => true,
        ], $overrides);
    }
}
