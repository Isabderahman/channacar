<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Reservation;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Feeds the fleet planning board: the fleet as rows and every reservation that
 * overlaps the requested window as a bar. Overlap (not containment) is what
 * matters here, so a rental that starts before the window still shows up.
 */
class PlanningController extends Controller
{
    private const MAX_DAYS = 120;

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date'],
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
            'include_cancelled' => ['sometimes', 'boolean'],
            'include_inactive' => ['sometimes', 'boolean'],
        ]);

        $from = $request->filled('from')
            ? CarbonImmutable::parse($request->string('from')->value())->startOfDay()
            : CarbonImmutable::now()->startOfMonth();

        $to = $request->filled('to')
            ? CarbonImmutable::parse($request->string('to')->value())->startOfDay()
            : $from->endOfMonth()->startOfDay();

        if ($to->lessThan($from)) {
            $to = $from;
        }

        // Hard cap so a wide range cannot pull the whole reservation history.
        if ($from->diffInDays($to) > self::MAX_DAYS - 1) {
            $to = $from->addDays(self::MAX_DAYS - 1);
        }

        $cars = Car::query()
            ->with('category:id,name')
            ->when(
                ! $request->boolean('include_inactive'),
                fn ($query) => $query->where('is_active', true)
            )
            ->when(
                $request->filled('category_id'),
                fn ($query) => $query->where('category_id', $request->integer('category_id'))
            )
            ->orderBy('brand')
            ->orderBy('name')
            ->get(['id', 'brand', 'name', 'registration_number', 'status', 'category_id']);

        $statuses = $request->boolean('include_cancelled')
            ? array_column(ReservationStatus::cases(), 'value')
            : [
                ReservationStatus::Pending->value,
                ReservationStatus::Confirmed->value,
                ReservationStatus::Ongoing->value,
                ReservationStatus::Completed->value,
            ];

        $reservations = Reservation::query()
            ->with('client:id,full_name,phone')
            ->whereIn('car_id', $cars->pluck('id'))
            ->whereIn('status', $statuses)
            ->whereDate('pickup_date', '<=', $to)
            ->whereDate('dropoff_date', '>=', $from)
            ->orderBy('pickup_date')
            ->get([
                'id',
                'reservation_number',
                'car_id',
                'client_id',
                'pickup_date',
                'pickup_time',
                'dropoff_date',
                'dropoff_time',
                'status',
                'payment_status',
                'total_price',
            ]);

        return response()->json([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'cars' => $cars->map(fn (Car $car) => [
                'id' => $car->id,
                'brand' => $car->brand,
                'name' => $car->name,
                'registration_number' => $car->registration_number,
                'status' => $car->status->value,
                'category' => $car->category?->name,
            ])->values(),
            'reservations' => $reservations->map(fn (Reservation $reservation) => [
                'id' => $reservation->id,
                'reservation_number' => $reservation->reservation_number,
                'car_id' => $reservation->car_id,
                'client_name' => $reservation->client?->full_name,
                'client_phone' => $reservation->client?->phone,
                'pickup_date' => $reservation->pickup_date?->toDateString(),
                'pickup_time' => $reservation->pickup_time,
                'dropoff_date' => $reservation->dropoff_date?->toDateString(),
                'dropoff_time' => $reservation->dropoff_time,
                'status' => $reservation->status->value,
                'payment_status' => $reservation->payment_status->value,
                'total_price' => $reservation->total_price,
            ])->values(),
        ]);
    }
}
