<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\CarStatus;
use App\Enums\FuelType;
use App\Enums\TransmissionType;
use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Car::query()
            ->with(['category', 'images'])
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->string('search')->trim()->value();

                $builder->where(function ($nested) use ($search) {
                    $nested
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->string('status')->value()))
            ->when($request->filled('is_active'), fn ($builder) => $builder->where('is_active', $request->boolean('is_active')))
            ->when($request->filled('category_id'), fn ($builder) => $builder->where('category_id', $request->integer('category_id')))
            ->latest();

        return response()->json($query->paginate($this->perPage($request)));
    }

    public function show(Car $car): JsonResponse
    {
        return response()->json([
            'data' => $car->load(['category', 'images']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());
        $validated['category_id'] = $this->resolveCategoryId($validated);
        unset($validated['category']);

        $car = Car::query()->create($validated)->load(['category', 'images']);

        return response()->json([
            'data' => $car,
        ], 201);
    }

    public function update(Request $request, Car $car): JsonResponse
    {
        $validated = $request->validate($this->rules($car, true));

        if (array_key_exists('category', $validated) || array_key_exists('category_id', $validated)) {
            $validated['category_id'] = $this->resolveCategoryId($validated);
            unset($validated['category']);
        }

        $car->update($validated);

        return response()->json([
            'data' => $car->fresh()->load(['category', 'images']),
        ]);
    }

    public function destroy(Car $car): JsonResponse
    {
        $paths = $car->images()->pluck('path')->all();

        try {
            $car->delete();
        } catch (QueryException) {
            return response()->json([
                'message' => 'This car cannot be deleted because it is linked to other records.',
            ], 409);
        }

        foreach ($paths as $path) {
            Storage::disk('public')->delete($path);
        }

        return response()->json([
            'message' => 'Car deleted successfully.',
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(?Car $car = null, bool $updating = false): array
    {
        $required = $updating ? 'sometimes' : 'required';
        $currentYear = now()->addYear()->year;

        return [
            'name' => [$required, 'string', 'max:255'],
            'brand' => [$required, 'string', 'max:255'],
            'year' => [$required, 'integer', 'min:1900', 'max:'.$currentYear],
            'category_id' => [$updating ? 'sometimes' : 'nullable', 'nullable', 'integer', 'exists:categories,id', 'required_without:category'],
            'category' => [$updating ? 'sometimes' : 'nullable', 'nullable', 'string', Rule::exists('categories', 'name'), 'required_without:category_id'],
            'doors' => [$required, 'integer', 'min:1', 'max:10'],
            'seats' => [$required, 'integer', 'min:1', 'max:20'],
            'luggage' => [$required, 'integer', 'min:0', 'max:20'],
            'transmission' => [$required, Rule::in($this->enumValues(TransmissionType::class))],
            'fuel' => [$required, Rule::in($this->enumValues(FuelType::class))],
            'climatisation' => [$required, 'boolean'],
            'gps' => [$required, 'boolean'],
            'base_price_per_day' => [$required, 'numeric', 'min:0'],
            'insurance_price_per_day' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'caution' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'status' => [$required, Rule::in($this->enumValues(CarStatus::class))],
            'registration_number' => [
                $required,
                'string',
                'max:255',
                Rule::unique('cars', 'registration_number')->ignore($car?->id),
            ],
            'is_active' => [$required, 'boolean'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolveCategoryId(array $validated): int
    {
        if (! empty($validated['category_id'])) {
            return (int) $validated['category_id'];
        }

        return Category::query()
            ->where('name', $validated['category'])
            ->firstOrFail()
            ->id;
    }

    /**
     * @return array<int, string>
     */
    private function enumValues(string $enumClass): array
    {
        return array_column($enumClass::cases(), 'value');
    }

    private function perPage(Request $request): int
    {
        return min(100, max(1, $request->integer('per_page', 15)));
    }
}
