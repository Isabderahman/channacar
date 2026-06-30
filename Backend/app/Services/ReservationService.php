<?php

namespace App\Services;

use App\Enums\CarStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReservationSource;
use App\Enums\ReservationStatus;
use App\Enums\SeasonPriceType;
use App\Models\Car;
use App\Models\CarSeasonPrice;
use App\Models\Client;
use App\Models\Extra;
use App\Models\PickupLocation;
use App\Models\Reservation;
use App\Models\Season;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    /**
     * Create an admin reservation.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createAdmin(array $attributes): Reservation
    {
        return DB::transaction(function () use ($attributes) {
            return $this->persistReservation($attributes);
        });
    }

    /**
     * Create a public reservation and client record when needed.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createPublic(array $attributes): Reservation
    {
        return DB::transaction(function () use ($attributes) {
            $client = $this->upsertClient($attributes['client']);

            return $this->persistReservation([
                'client_id' => $client->id,
                'car_id' => $attributes['car_id'],
                'pickup_location_id' => $attributes['pickup_location_id'],
                'dropoff_location_id' => $attributes['dropoff_location_id'],
                'pickup_date' => $attributes['pickup_date'],
                'pickup_time' => $attributes['pickup_time'],
                'dropoff_date' => $attributes['dropoff_date'],
                'dropoff_time' => $attributes['dropoff_time'],
                'status' => ReservationStatus::Pending->value,
                'payment_status' => PaymentStatus::Unpaid->value,
                'deposit_amount' => 0,
                'source' => ReservationSource::Web->value,
                'notes' => $attributes['notes'] ?? null,
                'extra_ids' => $attributes['extra_ids'] ?? $attributes['extras'] ?? [],
                'apply_insurance' => $attributes['apply_insurance'] ?? false,
            ]);
        });
    }

    /**
     * Update an existing reservation.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(Reservation $reservation, array $attributes): Reservation
    {
        return DB::transaction(function () use ($reservation, $attributes) {
            return $this->persistReservation($this->mergeReservationAttributes($reservation, $attributes), $reservation);
        });
    }

    public function updateStatus(Reservation $reservation, string $status): Reservation
    {
        return $this->update($reservation, ['status' => $status]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function persistReservation(array $attributes, ?Reservation $reservation = null): Reservation
    {
        $reservation ??= new Reservation();

        $pickupAt = $this->combineDateAndTime($attributes['pickup_date'], $attributes['pickup_time']);
        $dropoffAt = $this->combineDateAndTime($attributes['dropoff_date'], $attributes['dropoff_time']);

        $this->validateDateWindow($pickupAt, $dropoffAt);

        $status = ReservationStatus::from($attributes['status']);
        $paymentStatus = PaymentStatus::from($attributes['payment_status']);
        $source = ReservationSource::from($attributes['source']);
        $car = Car::query()->findOrFail($attributes['car_id']);

        if (! in_array($status, [ReservationStatus::Cancelled, ReservationStatus::Completed], true)) {
            $this->ensureCarAvailability($car, $pickupAt, $dropoffAt, $reservation->id);
        }

        $extraIds = $this->extractExtraIds($attributes);
        $applyInsurance = (bool) ($attributes['apply_insurance'] ?? false);
        $pricing = $this->calculatePricing(
            $car,
            $pickupAt,
            $dropoffAt,
            $extraIds,
            $applyInsurance,
            (int) $attributes['pickup_location_id'],
            (int) $attributes['dropoff_location_id'],
        );
        $previousStatus = $reservation->exists ? $this->enumValue($reservation->status) : null;

        $reservation->fill([
            'client_id' => $attributes['client_id'],
            'car_id' => $car->id,
            'pickup_location_id' => $attributes['pickup_location_id'],
            'dropoff_location_id' => $attributes['dropoff_location_id'],
            'pickup_date' => $pickupAt->toDateString(),
            'pickup_time' => $pickupAt->format('H:i:s'),
            'dropoff_date' => $dropoffAt->toDateString(),
            'dropoff_time' => $dropoffAt->format('H:i:s'),
            'status' => $status->value,
            'payment_status' => $paymentStatus->value,
            'deposit_amount' => $attributes['deposit_amount'],
            'total_price' => $pricing['total_price'],
            'insurance_total' => $pricing['insurance_total'],
            'delivery_total' => $pricing['delivery_total'],
            'source' => $source->value,
            'notes' => $attributes['notes'] ?? null,
        ]);

        if (! $reservation->exists) {
            $reservation->reservation_number = $this->generateReservationNumber();
        }

        $reservation->save();
        $reservation->extras()->sync($pricing['extras_sync']);

        $this->syncCarStatus($car, $previousStatus, $status);

        return $reservation->load($this->relations());
    }

    private function upsertClient(array $attributes): Client
    {
        $client = Client::query()
            ->where('driver_license', $attributes['driver_license'])
            ->first() ?? new Client();

        $client->fill($attributes);
        $client->save();

        return $client;
    }

    private function combineDateAndTime(string $date, string $time): Carbon
    {
        return Carbon::parse(sprintf('%s %s', $date, $time));
    }

    private function validateDateWindow(Carbon $pickupAt, Carbon $dropoffAt): void
    {
        if ($dropoffAt->lessThanOrEqualTo($pickupAt)) {
            throw ValidationException::withMessages([
                'dropoff_date' => ['The dropoff date and time must be after the pickup date and time.'],
            ]);
        }
    }

    private function ensureCarAvailability(Car $car, Carbon $pickupAt, Carbon $dropoffAt, ?int $ignoreReservationId = null): void
    {
        $candidates = Reservation::query()
            ->where('car_id', $car->id)
            ->when($ignoreReservationId, fn ($query) => $query->whereKeyNot($ignoreReservationId))
            ->whereIn('status', [
                ReservationStatus::Pending->value,
                ReservationStatus::Confirmed->value,
                ReservationStatus::Ongoing->value,
            ])
            ->whereDate('pickup_date', '<=', $dropoffAt->toDateString())
            ->whereDate('dropoff_date', '>=', $pickupAt->toDateString())
            ->get();

        foreach ($candidates as $candidate) {
            $candidatePickup = $this->combineDateAndTime(
                $candidate->pickup_date->toDateString(),
                $candidate->pickup_time
            );
            $candidateDropoff = $this->combineDateAndTime(
                $candidate->dropoff_date->toDateString(),
                $candidate->dropoff_time
            );

            if ($pickupAt->lt($candidateDropoff) && $dropoffAt->gt($candidatePickup)) {
                throw ValidationException::withMessages([
                    'car_id' => ['The selected car is not available for the requested period.'],
                ]);
            }
        }
    }

    /**
     * @param  array<int, int|string>  $extraIds
     * @return array{total_price:string, insurance_total:string, delivery_total:string, extras_sync:array<int, array{price_snapshot:string}>}
     */
    private function calculatePricing(
        Car $car,
        Carbon $pickupAt,
        Carbon $dropoffAt,
        array $extraIds,
        bool $applyInsurance = false,
        ?int $pickupLocationId = null,
        ?int $dropoffLocationId = null,
    ): array {
        $rentalDays = max(1, (int) ceil($pickupAt->diffInMinutes($dropoffAt) / 1440));
        $seasonCollection = Season::query()
            ->whereDate('date_from', '<=', $dropoffAt->toDateString())
            ->whereDate('date_to', '>=', $pickupAt->toDateString())
            ->orderByDesc('date_from')
            ->orderByDesc('id')
            ->get();

        $seasonOverrides = CarSeasonPrice::query()
            ->where('car_id', $car->id)
            ->whereIn('season_id', $seasonCollection->pluck('id'))
            ->pluck('price_per_day', 'season_id');

        $carTotal = 0.0;

        for ($day = 0; $day < $rentalDays; $day++) {
            $date = $pickupAt->copy()->addDays($day)->startOfDay();
            $carTotal += $this->resolveDailyRate($car, $date, $seasonCollection, $seasonOverrides->all());
        }

        $insuranceTotal = ($applyInsurance && $car->insurance_price_per_day !== null)
            ? (float) $car->insurance_price_per_day * $rentalDays
            : 0.0;

        // Frais de Livraison/Reprise: the pickup (livraison) fee plus the dropoff
        // (reprise) fee, each based on its city (Marrakech base = 0). Both are
        // charged independently — even when pickup and dropoff are the same city.
        $locationFees = PickupLocation::query()
            ->whereIn('id', array_filter([$pickupLocationId, $dropoffLocationId]))
            ->pluck('delivery_fee', 'id');

        $deliveryTotal = (float) ($locationFees[$pickupLocationId] ?? 0)
            + (float) ($locationFees[$dropoffLocationId] ?? 0);

        $extras = Extra::query()
            ->whereIn('id', $extraIds)
            ->get()
            ->keyBy('id');

        if (count($extraIds) !== $extras->count()) {
            throw ValidationException::withMessages([
                'extra_ids' => ['One or more selected extras are invalid.'],
            ]);
        }

        $extrasSync = [];
        $extrasTotal = 0.0;

        foreach ($extraIds as $extraId) {
            $extra = $extras->get((int) $extraId);

            if ($extra === null) {
                continue;
            }

            $snapshot = (float) $extra->price_per_day;
            $extrasSync[$extra->id] = [
                'price_snapshot' => number_format($snapshot, 2, '.', ''),
            ];
            $extrasTotal += $snapshot * $rentalDays;
        }

        return [
            'total_price' => number_format($carTotal + $extrasTotal + $insuranceTotal + $deliveryTotal, 2, '.', ''),
            'insurance_total' => number_format($insuranceTotal, 2, '.', ''),
            'delivery_total' => number_format($deliveryTotal, 2, '.', ''),
            'extras_sync' => $extrasSync,
        ];
    }

    /**
     * @param  array<int, Season>  $seasonCollection
     * @param  array<int, mixed>  $seasonOverrides
     */
    private function resolveDailyRate(Car $car, Carbon $date, $seasonCollection, array $seasonOverrides): float
    {
        foreach ($seasonCollection as $season) {
            if (
                $date->greaterThanOrEqualTo($season->date_from->copy()->startOfDay())
                && $date->lessThanOrEqualTo($season->date_to->copy()->endOfDay())
            ) {
                if (array_key_exists($season->id, $seasonOverrides)) {
                    return (float) $seasonOverrides[$season->id];
                }

                if ($season->price_type === SeasonPriceType::Fixed) {
                    return (float) $season->price_value;
                }

                return (float) $car->base_price_per_day * (float) $season->price_value;
            }
        }

        return (float) $car->base_price_per_day;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<int, int|string>
     */
    private function extractExtraIds(array $attributes): array
    {
        $extraIds = $attributes['extra_ids'] ?? $attributes['extras'] ?? [];

        Validator::make(['extra_ids' => $extraIds], [
            'extra_ids' => ['array'],
            'extra_ids.*' => ['integer'],
        ])->validate();

        return array_values(array_unique(array_map('intval', $extraIds)));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function mergeReservationAttributes(Reservation $reservation, array $attributes): array
    {
        return [
            'client_id' => $attributes['client_id'] ?? $reservation->client_id,
            'car_id' => $attributes['car_id'] ?? $reservation->car_id,
            'pickup_location_id' => $attributes['pickup_location_id'] ?? $reservation->pickup_location_id,
            'dropoff_location_id' => $attributes['dropoff_location_id'] ?? $reservation->dropoff_location_id,
            'pickup_date' => $attributes['pickup_date'] ?? $reservation->pickup_date->toDateString(),
            'pickup_time' => $attributes['pickup_time'] ?? $reservation->pickup_time,
            'dropoff_date' => $attributes['dropoff_date'] ?? $reservation->dropoff_date->toDateString(),
            'dropoff_time' => $attributes['dropoff_time'] ?? $reservation->dropoff_time,
            'status' => $attributes['status'] ?? $this->enumValue($reservation->status),
            'payment_status' => $attributes['payment_status'] ?? $this->enumValue($reservation->payment_status),
            'deposit_amount' => array_key_exists('deposit_amount', $attributes)
                ? $attributes['deposit_amount']
                : $reservation->deposit_amount,
            'source' => $attributes['source'] ?? $this->enumValue($reservation->source),
            'notes' => array_key_exists('notes', $attributes) ? $attributes['notes'] : $reservation->notes,
            'extra_ids' => $attributes['extra_ids']
                ?? $attributes['extras']
                ?? $reservation->extras()->pluck('extras.id')->all(),
            'apply_insurance' => array_key_exists('apply_insurance', $attributes)
                ? $attributes['apply_insurance']
                : ((float) $reservation->insurance_total > 0),
        ];
    }

    private function syncCarStatus(Car $car, ?string $previousStatus, ReservationStatus $newStatus): void
    {
        if ($car->status === CarStatus::Maintenance) {
            return;
        }

        if ($newStatus === ReservationStatus::Ongoing) {
            $car->update(['status' => CarStatus::Rented->value]);

            return;
        }

        if (
            in_array($newStatus, [ReservationStatus::Completed, ReservationStatus::Cancelled], true)
            || $previousStatus === ReservationStatus::Ongoing->value
        ) {
            $hasOngoing = Reservation::query()
                ->where('car_id', $car->id)
                ->where('status', ReservationStatus::Ongoing->value)
                ->exists();

            if (! $hasOngoing) {
                $car->update(['status' => CarStatus::Available->value]);
            }
        }
    }

    private function generateReservationNumber(): string
    {
        $year = now()->format('Y');
        $prefix = sprintf('CHN-%s-', $year);
        $latest = Reservation::query()
            ->where('reservation_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->latest('id')
            ->value('reservation_number');

        $sequence = 1;

        if (is_string($latest)) {
            $parts = explode('-', $latest);
            $sequence = ((int) end($parts)) + 1;
        }

        return sprintf('%s%04d', $prefix, $sequence);
    }

    /**
     * @return array<int, string>
     */
    private function relations(): array
    {
        return [
            'client',
            'car.category',
            'car.images',
            'pickupLocation',
            'dropoffLocation',
            'extras',
        ];
    }

    private function enumValue(mixed $value): mixed
    {
        return $value instanceof \BackedEnum
            ? $value->value
            : $value;
    }
}
