<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Admin-set tags on a car ("Nouveau", "Promo"), shown on the public site. */
class CarTagsTest extends TestCase
{
    use RefreshDatabase;

    private Car $car;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create());

        $category = Category::query()->create(['name' => 'SUV']);
        $this->car = Car::query()->create([
            'name' => 'Touareg', 'brand' => 'Volkswagen', 'year' => 2024, 'category_id' => $category->id,
            'doors' => 5, 'seats' => 5, 'luggage' => 3, 'transmission' => 'auto', 'fuel' => 'diesel',
            'climatisation' => true, 'gps' => true, 'base_price_per_day' => 1350, 'status' => 'available',
            'registration_number' => 'TAG-1', 'is_active' => true,
        ]);
    }

    public function test_tags_are_cleaned_and_deduplicated(): void
    {
        $this->putJson("/api/cars/{$this->car->id}", ['tags' => ['  Promo ', 'promo', 'Best   seller', '']])
            ->assertOk()
            ->assertJsonPath('data.tags', ['Promo', 'Best seller']);
    }

    public function test_clearing_tags_stores_null(): void
    {
        $this->car->update(['tags' => ['Promo']]);

        $this->putJson("/api/cars/{$this->car->id}", ['tags' => []])
            ->assertOk()
            ->assertJsonPath('data.tags', null);
    }

    public function test_tag_limits_are_validated(): void
    {
        $this->putJson("/api/cars/{$this->car->id}", ['tags' => array_map(fn ($i) => "Tag {$i}", range(1, Car::MAX_TAGS + 1))])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tags');

        $this->putJson("/api/cars/{$this->car->id}", ['tags' => [str_repeat('x', Car::MAX_TAG_LENGTH + 1)]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tags.0');
    }

    public function test_tags_are_public_and_suggested_to_the_admin(): void
    {
        $this->car->update(['tags' => ['Promo', 'Familiale']]);

        $this->getJson('/api/public/cars')->assertJsonPath('data.0.tags', ['Promo', 'Familiale']);
        $this->getJson('/api/car-tags')->assertExactJson(['data' => ['Familiale', 'Promo']]);
    }
}
