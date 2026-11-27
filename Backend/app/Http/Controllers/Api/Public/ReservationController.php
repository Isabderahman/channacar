<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Mail\NewReservationNotification;
use App\Services\MetaConversionsService;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationService $reservationService,
        private readonly MetaConversionsService $metaConversions,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'car_id' => ['required', 'integer', 'exists:cars,id'],
            'pickup_location_id' => ['required', 'integer', 'exists:pickup_locations,id'],
            'dropoff_location_id' => ['required', 'integer', 'exists:pickup_locations,id'],
            'pickup_date' => ['required', 'date'],
            'pickup_time' => ['required', 'date_format:H:i'],
            'dropoff_date' => ['required', 'date'],
            'dropoff_time' => ['required', 'date_format:H:i'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'apply_insurance' => ['sometimes', 'boolean'],
            'extra_ids' => ['sometimes', 'array'],
            'extra_ids.*' => ['integer', 'exists:extras,id'],
            'extras' => ['sometimes', 'array'],
            'extras.*' => ['integer', 'exists:extras,id'],
            'client' => ['required', 'array'],
            'client.full_name' => ['required', 'string', 'max:255'],
            'client.phone' => ['required', 'string', 'max:255'],
            'client.whatsapp' => ['sometimes', 'nullable', 'string', 'max:255'],
            'client.email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'client.driver_license' => ['required', 'string', 'max:255'],
            // Browser context for the Meta Conversions API (see MetaConversionsService).
            'tracking' => ['sometimes', 'nullable', 'array'],
            'tracking.event_id' => ['sometimes', 'nullable', 'string', 'max:100'],
            'tracking.source_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'tracking.fbp' => ['sometimes', 'nullable', 'string', 'max:255'],
            'tracking.fbc' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $reservation = $this->reservationService->createPublic($validated);

        try {
            Mail::send(new NewReservationNotification($reservation));
        } catch (\Throwable $e) {
            Log::error('Failed to send new reservation notification', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
        }

        if ($this->metaConversions->enabled()) {
            $metaEvent = $this->metaConversions->reservationPurchaseEvent(
                $reservation,
                $request,
                $validated['tracking'] ?? [],
            );

            // Runs once the response has been flushed to the visitor, so
            // Meta's latency never slows the booking down.
            app()->terminating(fn () => $this->metaConversions->send($metaEvent));
        }

        return response()->json([
            'data' => $reservation,
        ], 201);
    }
}
