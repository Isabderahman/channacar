<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            Testimonial::query()
                ->where('is_visible', true)
                ->latest()
                ->paginate($this->perPage($request))
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'stars' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $testimonial = Testimonial::query()->create([
            ...$validated,
            'is_visible' => false,
        ]);

        return response()->json([
            'data' => $testimonial,
        ], 201);
    }

    private function perPage(Request $request): int
    {
        return min(100, max(1, $request->integer('per_page', 6)));
    }
}
