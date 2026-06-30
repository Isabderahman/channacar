<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\SeasonPriceType;
use App\Http\Controllers\Controller;
use App\Models\Season;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SeasonController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            Season::query()->latest()->paginate($this->perPage($request))
        );
    }

    public function show(Season $season): JsonResponse
    {
        return response()->json(['data' => $season]);
    }

    public function store(Request $request): JsonResponse
    {
        $season = Season::query()->create($request->validate($this->rules()));

        return response()->json(['data' => $season], 201);
    }

    public function update(Request $request, Season $season): JsonResponse
    {
        $season->update($request->validate($this->rules(true)));

        return response()->json(['data' => $season->fresh()]);
    }

    public function destroy(Season $season): JsonResponse
    {
        try {
            $season->delete();
        } catch (QueryException) {
            return response()->json([
                'message' => 'This season cannot be deleted because it is linked to other records.',
            ], 409);
        }

        return response()->json(['message' => 'Season deleted successfully.']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(bool $updating = false): array
    {
        $required = $updating ? 'sometimes' : 'required';

        return [
            'name' => [$required, 'string', 'max:255'],
            'date_from' => [$required, 'date'],
            'date_to' => [$required, 'date', 'after_or_equal:date_from'],
            'price_type' => [$required, Rule::in($this->enumValues(SeasonPriceType::class))],
            'price_value' => [$required, 'numeric', 'min:0'],
        ];
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
