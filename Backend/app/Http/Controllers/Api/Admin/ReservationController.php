<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\PaymentStatus;
use App\Enums\ReservationSource;
use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    public function __construct(private readonly ReservationService $reservationService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $query = Reservation::query()
            ->with(['client', 'car.category', 'car.images', 'pickupLocation', 'dropoffLocation', 'extras'])
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->string('status')->value()))
            ->when($request->filled('payment_status'), fn ($builder) => $builder->where('payment_status', $request->string('payment_status')->value()))
            ->when($request->filled('source'), fn ($builder) => $builder->where('source', $request->string('source')->value()))
            ->when($request->filled('car_id'), fn ($builder) => $builder->where('car_id', $request->integer('car_id')))
            ->when($request->filled('client_id'), fn ($builder) => $builder->where('client_id', $request->integer('client_id')))
            ->when($request->filled('from_date'), fn ($builder) => $builder->whereDate('pickup_date', '>=', $request->date('from_date')))
            ->when($request->filled('to_date'), fn ($builder) => $builder->whereDate('dropoff_date', '<=', $request->date('to_date')))
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->string('search')->trim()->value();

                $builder->where(function ($nested) use ($search) {
                    $nested
                        ->where('reservation_number', 'like', "%{$search}%")
                        ->orWhereHas('client', function ($clientQuery) use ($search) {
                            $clientQuery
                                ->where('full_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('driver_license', 'like', "%{$search}%");
                        });
                });
            })
            ->latest();

        return response()->json($query->paginate($this->perPage($request)));
    }

    public function show(Reservation $reservation): JsonResponse
    {
        return response()->json([
            'data' => $reservation->load(['client', 'car.category', 'car.images', 'pickupLocation', 'dropoffLocation', 'extras']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $reservation = $this->reservationService->createAdmin(
            $request->validate($this->rules())
        );

        return response()->json([
            'data' => $reservation,
        ], 201);
    }

    public function update(Request $request, Reservation $reservation): JsonResponse
    {
        $updatedReservation = $this->reservationService->update(
            $reservation,
            $request->validate($this->rules(true))
        );

        return response()->json([
            'data' => $updatedReservation,
        ]);
    }

    public function updateStatus(Request $request, Reservation $reservation): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in($this->enumValues(ReservationStatus::class))],
        ]);

        $updatedReservation = $this->reservationService->updateStatus($reservation, $validated['status']);

        return response()->json([
            'data' => $updatedReservation,
        ]);
    }

    public function destroy(Reservation $reservation): JsonResponse
    {
        try {
            $reservation->delete();
        } catch (QueryException) {
            return response()->json([
                'message' => 'This reservation cannot be deleted because it is linked to other records.',
            ], 409);
        }

        return response()->json([
            'message' => 'Reservation deleted successfully.',
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(bool $updating = false): array
    {
        $required = $updating ? 'sometimes' : 'required';

        return [
            'client_id' => [$required, 'integer', 'exists:clients,id'],
            'car_id' => [$required, 'integer', 'exists:cars,id'],
            'pickup_location_id' => [$required, 'integer', 'exists:pickup_locations,id'],
            'dropoff_location_id' => [$required, 'integer', 'exists:pickup_locations,id'],
            'pickup_date' => [$required, 'date'],
            'pickup_time' => [$required, 'date_format:H:i'],
            'dropoff_date' => [$required, 'date'],
            'dropoff_time' => [$required, 'date_format:H:i'],
            'status' => [$required, Rule::in($this->enumValues(ReservationStatus::class))],
            'payment_status' => [$required, Rule::in($this->enumValues(PaymentStatus::class))],
            'deposit_amount' => [$required, 'numeric', 'min:0'],
            'source' => [$required, Rule::in($this->enumValues(ReservationSource::class))],
            'notes' => ['sometimes', 'nullable', 'string'],
            'apply_insurance' => ['sometimes', 'boolean'],
            'extra_ids' => ['sometimes', 'array'],
            'extra_ids.*' => ['integer', 'exists:extras,id'],
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
