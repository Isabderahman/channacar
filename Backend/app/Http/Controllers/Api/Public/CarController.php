<?php

namespace App\Http\Controllers\Api\Public;

use App\Enums\CarStatus;
use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Car::query()
            ->with(['category', 'images'])
            ->where('is_active', true)
            ->where('status', CarStatus::Available->value)
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->string('search')->trim()->value();

                $builder->where(function ($nested) use ($search) {
                    $nested
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category_id'), fn ($builder) => $builder->where('category_id', $request->integer('category_id')))
            ->when($request->filled('transmission'), fn ($builder) => $builder->where('transmission', $request->string('transmission')->value()))
            ->when($request->filled('fuel'), fn ($builder) => $builder->where('fuel', $request->string('fuel')->value()))
            ->when($request->filled('seats'), fn ($builder) => $builder->where('seats', '>=', $request->integer('seats')))
            ->when($request->filled('min_price'), fn ($builder) => $builder->where('base_price_per_day', '>=', $request->input('min_price')))
            ->when($request->filled('max_price'), fn ($builder) => $builder->where('base_price_per_day', '<=', $request->input('max_price')))
            ->latest();

        return response()->json($query->paginate($this->perPage($request)));
    }

    public function show(string $car): JsonResponse
    {
        $model = Car::query()
            ->with(['category', 'images'])
            ->where('slug', $car)
            ->where('is_active', true)
            ->where('status', CarStatus::Available->value)
            ->firstOrFail();

        return response()->json([
            'data' => $model,
        ]);
    }

    private function perPage(Request $request): int
    {
        return min(100, max(1, $request->integer('per_page', 12)));
    }
}
