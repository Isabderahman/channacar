<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The `api` rate limiter (RouteServiceProvider) runs before `auth:sanctum`.
 * It used to key every admin request by IP — and behind the Nuxt proxy all
 * traffic shares one IP — so uploading a large photo gallery (one request per
 * image) hit 429 "Too Many Attempts." part-way through.
 */
class ApiRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_in_admin_gets_more_than_sixty_requests_a_minute(): void
    {
        Sanctum::actingAs(User::factory()->create());

        for ($request = 1; $request <= 61; $request++) {
            $this->getJson('/api/locations')->assertOk();
        }
    }

    public function test_anonymous_requests_keep_the_sixty_per_minute_limit(): void
    {
        for ($request = 1; $request <= 60; $request++) {
            $this->getJson('/api/public/locations')->assertOk();
        }

        $this->getJson('/api/public/locations')->assertStatus(429);
    }
}
