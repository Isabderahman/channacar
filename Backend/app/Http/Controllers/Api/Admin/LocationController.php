<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PickupLocation;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PickupLocation::query()
            ->when($request->filled('is_active'), fn ($builder) => $builder->where('is_active', $request->boolean('is_active')))
            ->latest('id');

        return response()->json($query->paginate($this->perPage($request)));
    }

    public function show(PickupLocation $location): JsonResponse
    {
        return response()->json(['data' => $location]);
    }

    public function store(Request $request): JsonResponse
    {
        $location = PickupLocation::query()->create($request->validate($this->rules()));

        return response()->json(['data' => $location], 201);
    }

    public function update(Request $request, PickupLocation $location): JsonResponse
    {
        $location->update($request->validate($this->rules(true)));

        return response()->json(['data' => $location->fresh()]);
    }

    public function destroy(PickupLocation $location): JsonResponse
    {
        try {
            $location->delete();
        } catch (QueryException) {
            return response()->json([
                'message' => 'This location cannot be deleted because it is linked to other records.',
            ], 409);
        }

        return response()->json(['message' => 'Location deleted successfully.']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(bool $updating = false): array
    {
        $required = $updating ? 'sometimes' : 'required';

        return [
            'name' => [$required, 'string', 'max:255'],
            'address' => [$required, 'string'],
            'delivery_fee' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'is_active' => [$required, 'boolean'],
        ];
    }

    private function perPage(Request $request): int
    {
        return min(100, max(1, $request->integer('per_page', 15)));
    }
}
