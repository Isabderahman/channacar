<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Testimonial::query()
            ->when($request->filled('is_visible'), fn ($builder) => $builder->where('is_visible', $request->boolean('is_visible')))
            ->latest();

        return response()->json($query->paginate($this->perPage($request)));
    }

    public function show(Testimonial $testimonial): JsonResponse
    {
        return response()->json(['data' => $testimonial]);
    }

    public function store(Request $request): JsonResponse
    {
        $testimonial = Testimonial::query()->create($request->validate($this->rules()));

        return response()->json(['data' => $testimonial], 201);
    }

    public function update(Request $request, Testimonial $testimonial): JsonResponse
    {
        $testimonial->update($request->validate($this->rules(true)));

        return response()->json(['data' => $testimonial->fresh()]);
    }

    public function destroy(Testimonial $testimonial): JsonResponse
    {
        $testimonial->delete();

        return response()->json(['message' => 'Testimonial deleted successfully.']);
    }

    public function updateVisibility(Request $request, Testimonial $testimonial): JsonResponse
    {
        $validated = $request->validate([
            'is_visible' => ['required', 'boolean'],
        ]);

        $testimonial->update($validated);

        return response()->json(['data' => $testimonial->fresh()]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(bool $updating = false): array
    {
        $required = $updating ? 'sometimes' : 'required';

        return [
            'full_name' => [$required, 'string', 'max:255'],
            'content' => [$required, 'string'],
            'stars' => [$required, 'integer', 'min:1', 'max:5'],
            'is_visible' => [$required, 'boolean'],
        ];
    }

    private function perPage(Request $request): int
    {
        return min(100, max(1, $request->integer('per_page', 15)));
    }
}
