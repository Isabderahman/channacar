<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Client::query()
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->string('search')->trim()->value();

                $builder->where(function ($nested) use ($search) {
                    $nested
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('whatsapp', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('driver_license', 'like', "%{$search}%");
                });
            })
            ->latest();

        return response()->json($query->paginate($this->perPage($request)));
    }

    public function show(Client $client): JsonResponse
    {
        return response()->json([
            'data' => $client->load('reservations'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $client = Client::query()->create($request->validate($this->rules()));

        return response()->json(['data' => $client], 201);
    }

    public function update(Request $request, Client $client): JsonResponse
    {
        $client->update($request->validate($this->rules(true)));

        return response()->json(['data' => $client->fresh()]);
    }

    public function destroy(Client $client): JsonResponse
    {
        try {
            $client->delete();
        } catch (QueryException) {
            return response()->json([
                'message' => 'This client cannot be deleted because it is linked to other records.',
            ], 409);
        }

        return response()->json(['message' => 'Client deleted successfully.']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(bool $updating = false): array
    {
        $required = $updating ? 'sometimes' : 'required';

        return [
            'full_name' => [$required, 'string', 'max:255'],
            'phone' => [$required, 'string', 'max:255'],
            'whatsapp' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'driver_license' => [$required, 'string', 'max:255'],
        ];
    }

    private function perPage(Request $request): int
    {
        return min(100, max(1, $request->integer('per_page', 15)));
    }
}
