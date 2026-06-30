<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ContractController extends Controller
{
    /**
     * Save the corrected contract information (client details, 2nd driver,
     * payment method) that the admin reviews before generating the contract.
     */
    public function update(Request $request, Reservation $reservation): JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => ['sometimes', 'nullable', 'in:cash,cheque'],
            'second_driver' => ['sometimes', 'nullable', 'array'],
            'contract_details' => ['sometimes', 'nullable', 'array'],
            'client' => ['sometimes', 'array'],
            'client.full_name' => ['sometimes', 'string', 'max:255'],
            'client.birth_date' => ['sometimes', 'nullable', 'date'],
            'client.birth_place' => ['sometimes', 'nullable', 'string', 'max:255'],
            'client.address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'client.phone' => ['sometimes', 'nullable', 'string', 'max:255'],
            'client.whatsapp' => ['sometimes', 'nullable', 'string', 'max:255'],
            'client.email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'client.driver_license' => ['sometimes', 'nullable', 'string', 'max:255'],
            'client.license_issued_at' => ['sometimes', 'nullable', 'date'],
            'client.license_issued_place' => ['sometimes', 'nullable', 'string', 'max:255'],
            'client.passport_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'client.cin_number' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        if (isset($validated['client']) && $reservation->client) {
            $reservation->client->fill($validated['client'])->save();
        }

        if (array_key_exists('payment_method', $validated)) {
            $reservation->payment_method = $validated['payment_method'];
        }

        if (array_key_exists('second_driver', $validated)) {
            $reservation->second_driver = $this->cleanSecondDriver($validated['second_driver'] ?? null);
        }

        if (array_key_exists('contract_details', $validated)) {
            $reservation->contract_details = $this->cleanContractDetails($validated['contract_details'] ?? null);
        }

        $reservation->save();

        return response()->json(['data' => $reservation->fresh($this->relations())]);
    }

    /**
     * Upload the customer's driver licence and identity (CIN / passport) scans.
     */
    public function uploadDocuments(Request $request, Reservation $reservation): JsonResponse
    {
        // Each document is recto (front) + verso (back).
        $fields = [
            'driver_license' => 'driver_license_path',
            'driver_license_verso' => 'driver_license_verso_path',
            'identity' => 'identity_path',
            'identity_verso' => 'identity_verso_path',
        ];

        $request->validate(
            collect($fields)->mapWithKeys(fn ($column, $input) => [
                $input => ['sometimes', 'file', 'image', 'max:8192'],
            ])->all()
        );

        try {
            foreach ($fields as $input => $column) {
                if ($request->hasFile($input)) {
                    $this->replaceFile($reservation->{$column});
                    $reservation->{$column} = $request->file($input)->store('contracts', 'public');
                }
            }

            $reservation->save();
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Les documents n\'ont pas pu être enregistrés. Réessayez.',
            ], 500);
        }

        return response()->json(['data' => $reservation->fresh($this->relations())]);
    }

    /**
     * Generate and download the rental contract PDF. The contract number is
     * assigned on first generation.
     */
    public function download(Reservation $reservation): Response
    {
        $reservation->load($this->relations());

        if (! $reservation->client) {
            return response()->json([
                'message' => 'Aucun client n\'est associé à cette réservation. Impossible de générer le contrat.',
            ], 422);
        }

        try {
            if (! $reservation->contract_number) {
                DB::transaction(function () use ($reservation) {
                    $reservation->contract_number = $this->nextContractNumber();
                    $reservation->contract_generated_at = now();
                    $reservation->save();
                });
            }

            $pdf = Pdf::loadView('contract', $this->contractData($reservation))->setPaper('a4');

            return $pdf->download('contrat-'.$reservation->contract_number.'.pdf');
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Le contrat n\'a pas pu être généré : '.$exception->getMessage(),
            ], 500);
        }
    }

    /**
     * @param  array<string, mixed>|null  $input
     * @return array<string, mixed>|null
     */
    private function cleanSecondDriver(?array $input): ?array
    {
        if (! $input) {
            return null;
        }

        $keys = [
            'full_name', 'birth_date', 'birth_place', 'address', 'phone',
            'driver_license', 'license_issued_at', 'license_issued_place',
            'passport_number', 'cin_number',
        ];

        $clean = [];
        foreach ($keys as $key) {
            if (! empty($input[$key])) {
                $clean[$key] = $input[$key];
            }
        }

        return $clean ?: null;
    }

    /**
     * @param  array<string, mixed>|null  $input
     * @return array<string, mixed>|null
     */
    private function cleanContractDetails(?array $input): ?array
    {
        if (! $input) {
            return null;
        }

        $keys = [
            'prolongation_date', 'prolongation_time', 'prolongation_location',
            'km_depart', 'km_arrivee', 'fuel_depart', 'fuel_retour',
            'condition_depart', 'condition_retour', 'personnes_transportees',
            'suppression_franchise', 'divers_note',
        ];

        $clean = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $input) && $input[$key] !== null && $input[$key] !== '') {
                $clean[$key] = $input[$key];
            }
        }

        return $clean ?: null;
    }

    private function replaceFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function nextContractNumber(): string
    {
        $latest = Reservation::query()
            ->whereNotNull('contract_number')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('contract_number');

        $sequence = $latest ? ((int) $latest) + 1 : 1;

        return str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * @return array<string, mixed>
     */
    private function contractData(Reservation $reservation): array
    {
        $pickupAt = Carbon::parse($reservation->pickup_date->toDateString().' '.$reservation->pickup_time);
        $dropoffAt = Carbon::parse($reservation->dropoff_date->toDateString().' '.$reservation->dropoff_time);
        $days = max(1, (int) ceil($pickupAt->diffInMinutes($dropoffAt) / 1440));

        $extrasTotal = 0.0;
        foreach ($reservation->extras as $extra) {
            $extrasTotal += (float) $extra->pivot->price_snapshot * $days;
        }

        $insurance = (float) $reservation->insurance_total;
        $delivery = (float) $reservation->delivery_total;
        $total = (float) $reservation->total_price;
        $carNet = max(0, $total - $extrasTotal - $insurance - $delivery);

        return [
            'reservation' => $reservation,
            'days' => $days,
            'pickupAt' => $pickupAt,
            'dropoffAt' => $dropoffAt,
            'dailyRate' => $days > 0 ? $carNet / $days : $carNet,
            'carNet' => $carNet,
            'divers' => $extrasTotal + $insurance,
            'delivery' => $delivery,
            'total' => $total,
            'tva' => $total - ($total / 1.2),
            'details' => $reservation->contract_details ?? [],
            'driverLicenseImage' => $this->imageData($reservation->driver_license_path),
            'driverLicenseVersoImage' => $this->imageData($reservation->driver_license_verso_path),
            'identityImage' => $this->imageData($reservation->identity_path),
            'identityVersoImage' => $this->imageData($reservation->identity_verso_path),
        ];
    }

    private function imageData(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $full = Storage::disk('public')->path($path);
        if (! is_file($full)) {
            return null;
        }

        return 'data:'.mime_content_type($full).';base64,'.base64_encode((string) file_get_contents($full));
    }

    /**
     * @return array<int, string>
     */
    private function relations(): array
    {
        return ['client', 'car.category', 'pickupLocation', 'dropoffLocation', 'extras'];
    }
}
