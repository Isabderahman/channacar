<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Extra;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExtraController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Extra::query()
            ->when($request->filled('is_active'), fn ($builder) => $builder->where('is_active', $request->boolean('is_active')))
            ->latest('id');

        return response()->json($query->paginate($this->perPage($request)));
    }

    public function show(Extra $extra): JsonResponse
    {
        return response()->json(['data' => $extra]);
    }

    public function store(Request $request): JsonResponse
    {
        $extra = Extra::query()->create($request->validate($this->rules()));

        return response()->json(['data' => $extra], 201);
    }

    public function update(Request $request, Extra $extra): JsonResponse
    {
        $extra->update($request->validate($this->rules(true, $extra)));

        return response()->json(['data' => $extra->fresh()]);
    }

    public function destroy(Extra $extra): JsonResponse
    {
        try {
            $extra->delete();
        } catch (QueryException) {
            return response()->json([
                'message' => 'This extra cannot be deleted because it is linked to other records.',
            ], 409);
        }

        return response()->json(['message' => 'Extra deleted successfully.']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(bool $updating = false, ?Extra $extra = null): array
    {
        $required = $updating ? 'sometimes' : 'required';

        return [
            'name' => [
                $required,
                'string',
                'max:255',
                Rule::unique('extras', 'name')->ignore($extra?->id),
            ],
            'icon' => ['sometimes', 'nullable', 'string', 'max:255'],
            'price_per_day' => [$required, 'numeric', 'min:0'],
            'is_active' => [$required, 'boolean'],
        ];
    }

    private function perPage(Request $request): int
    {
        return min(100, max(1, $request->integer('per_page', 15)));
    }
}
