<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Meta (Facebook) Conversions API: sends website events server-side, so a
 * booking still reaches Meta when the browser pixel is blocked.
 *
 * Each server event carries the same `event_id` as its browser twin
 * (`fbq('track', …, { eventID })` in the frontend), which lets Meta merge the
 * pair into a single conversion. Personal data is normalized and SHA-256
 * hashed before it leaves the server, as Meta requires.
 */
class MetaConversionsService
{
    public function enabled(): bool
    {
        return filled(config('services.meta.pixel_id')) && filled(config('services.meta.access_token'));
    }

    /**
     * Purchase event for a reservation booked on the public site.
     *
     * Built while the request is still available (IP, user agent); send it
     * later with send().
     *
     * @param  array{event_id?: ?string, source_url?: ?string, fbp?: ?string, fbc?: ?string}  $tracking
     *                                                                                                   Browser context posted by the booking form.
     * @return array<string, mixed>
     */
    public function reservationPurchaseEvent(Reservation $reservation, Request $request, array $tracking = []): array
    {
        $reservation->loadMissing(['client', 'car']);
        $client = $reservation->client;
        [$firstName, $lastName] = $this->splitName($client?->full_name);

        $carName = trim(($reservation->car?->brand ?? '').' '.($reservation->car?->name ?? ''));

        return [
            'event_name' => 'Purchase',
            'event_time' => now()->getTimestamp(),
            // Must equal the browser event's eventID for deduplication. The
            // fallback only kicks in for a client that didn't send one.
            'event_id' => ($tracking['event_id'] ?? null) ?: 'reservation-'.$reservation->reservation_number,
            'event_source_url' => ($tracking['source_url'] ?? null) ?: $request->headers->get('referer'),
            'action_source' => 'website',
            'user_data' => array_filter([
                'em' => $this->hashed($this->normalizeEmail($client?->email)),
                'ph' => $this->hashed($this->normalizePhone($client?->phone)),
                'fn' => $this->hashed($firstName),
                'ln' => $this->hashed($lastName),
                'client_ip_address' => $this->clientIp($request),
                'client_user_agent' => $request->userAgent(),
                'fbp' => $tracking['fbp'] ?? null,
                'fbc' => $tracking['fbc'] ?? null,
            ]),
            'custom_data' => array_filter([
                'currency' => 'MAD',
                'value' => round((float) $reservation->total_price, 2),
                'content_ids' => [(string) $reservation->car_id],
                'content_type' => 'product',
                'content_name' => $carName !== '' ? $carName : null,
                'order_id' => $reservation->reservation_number,
            ], fn ($value) => $value !== null),
        ];
    }

    /**
     * Post events to the Graph API. Never throws: tracking must not break
     * the action that triggered it, so failures are only logged.
     *
     * @param  array<string, mixed>  ...$events
     */
    public function send(array ...$events): void
    {
        if (! $this->enabled() || $events === []) {
            return;
        }

        $url = sprintf(
            'https://graph.facebook.com/%s/%s/events',
            config('services.meta.graph_version'),
            config('services.meta.pixel_id'),
        );

        try {
            // Form-encoded with the token in the body (not the URL), as in
            // Meta's Conversions API reference.
            $response = Http::asForm()
                ->timeout(5)
                ->post($url, array_filter([
                    'data' => json_encode($events, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'test_event_code' => config('services.meta.test_event_code'),
                    'access_token' => config('services.meta.access_token'),
                ]));

            if ($response->failed()) {
                Log::warning('Meta Conversions API rejected events', [
                    'events' => array_column($events, 'event_name'),
                    'status' => $response->status(),
                    'error' => $response->json('error.message') ?? $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Meta Conversions API request failed', [
                'events' => array_column($events, 'event_name'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * The visitor's address. Public bookings travel nginx → Nuxt proxy →
     * nginx → PHP-FPM, so $request->ip() is the Nuxt container; the edge nginx
     * passes the real address in X-Real-IP / X-Forwarded-For.
     */
    private function clientIp(Request $request): ?string
    {
        $forwarded = explode(',', (string) $request->header('X-Forwarded-For'));

        $candidates = [
            $request->header('X-Real-IP'),
            trim($forwarded[0]),
            $request->ip(),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate)
                && filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $candidate;
            }
        }

        return null;
    }

    private function normalizeEmail(?string $email): ?string
    {
        $email = mb_strtolower(trim((string) $email));

        return $email !== '' ? $email : null;
    }

    /**
     * Digits only, with the country code and no leading zeros
     * ("06 12 34 56 78" → "212612345678"). Numbers without a country code are
     * assumed Moroccan.
     */
    private function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $digits = '212'.substr($digits, 1);
        } elseif (strlen($digits) === 9 && in_array($digits[0], ['5', '6', '7'], true)) {
            $digits = '212'.$digits;
        }

        // "+212 0 6…": the trunk zero is sometimes kept after the country code.
        if (str_starts_with($digits, '2120')) {
            $digits = '212'.substr($digits, 4);
        }

        return strlen($digits) >= 8 ? $digits : null;
    }

    /**
     * @return array{0: ?string, 1: ?string} [first name, last name], lowercase
     */
    private function splitName(?string $fullName): array
    {
        $parts = preg_split('/\s+/u', mb_strtolower(trim((string) $fullName)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return [
            $parts[0] ?? null,
            count($parts) > 1 ? $parts[count($parts) - 1] : null,
        ];
    }

    /**
     * @return list<string>|null
     */
    private function hashed(?string $value): ?array
    {
        return $value === null || $value === '' ? null : [hash('sha256', $value)];
    }
}
