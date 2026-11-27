<?php

namespace App\Services;

use App\Enums\BillingDocumentType;
use App\Models\BillingDocument;
use App\Support\FrenchAmountInWords;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Creates and recalculates devis / factures. Totals are always computed here
 * from the lines (prices are HT): Total HT → TVA → Total TTC.
 */
class BillingDocumentService
{
    public const LINE_PER_DAY = 'per_day';

    public const LINE_FLAT = 'flat';

    /**
     * @param  array<string, mixed>  $data  validated request data, `type` included
     */
    public function create(array $data): BillingDocument
    {
        $type = BillingDocumentType::from($data['type']);
        $attributes = $this->attributes($data, $type);

        return DB::transaction(function () use ($type, $attributes) {
            $year = Carbon::parse($attributes['issue_date'])->year;
            $sequence = $this->nextSequence($type, $year);

            return BillingDocument::query()->create([
                ...$attributes,
                'type' => $type,
                'year' => $year,
                'sequence' => $sequence,
                'number' => sprintf('%s-%d-%04d', $type->prefix(), $year, $sequence),
            ]);
        });
    }

    /**
     * Type and number never change once issued.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(BillingDocument $document, array $data): BillingDocument
    {
        $document->fill($this->attributes($data, $document->type))->save();

        return $document;
    }

    /**
     * Turn an accepted devis into a facture dated today, with the same client,
     * lines and TVA. A devis converts once.
     */
    public function convertToInvoice(BillingDocument $quote): BillingDocument
    {
        if ($quote->type !== BillingDocumentType::Quote) {
            throw ValidationException::withMessages([
                'type' => 'Seul un devis peut être converti en facture.',
            ]);
        }

        return DB::transaction(function () use ($quote) {
            $existing = BillingDocument::query()
                ->where('source_document_id', $quote->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'source_document_id' => "Ce devis a déjà été converti en facture ({$existing->number}).",
                ]);
            }

            $invoice = $this->create([
                'type' => BillingDocumentType::Invoice->value,
                'issue_date' => now()->toDateString(),
                'client_name' => $quote->client_name,
                'client_details' => $quote->client_details,
                'lines' => $quote->lines,
                'tva_rate' => $quote->tva_rate,
                'notes' => $quote->notes,
            ]);

            $invoice->forceFill(['source_document_id' => $quote->id])->save();

            return $invoice;
        });
    }

    public function amountInWords(BillingDocument $document): string
    {
        return FrenchAmountInWords::dirhams((float) $document->total_ttc);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data, BillingDocumentType $type): array
    {
        $lines = $this->normalizeLines($data['lines'] ?? []);
        $tvaRate = round((float) ($data['tva_rate'] ?? config('company.default_tva_rate')), 2);

        $totalHt = round(array_sum(array_column($lines, 'total')), 2);

        if ($totalHt < 0) {
            throw ValidationException::withMessages([
                'lines' => 'Le total HT ne peut pas être négatif.',
            ]);
        }

        $totalTva = round($totalHt * $tvaRate / 100, 2);

        return [
            'issue_date' => Carbon::parse($data['issue_date'])->toDateString(),
            'validity_days' => $type === BillingDocumentType::Quote
                ? (int) ($data['validity_days'] ?? config('company.default_quote_validity_days'))
                : null,
            'client_name' => trim((string) $data['client_name']),
            'client_details' => $this->cleanText($data['client_details'] ?? null),
            'lines' => $lines,
            'tva_rate' => $tvaRate,
            'total_ht' => $totalHt,
            'total_tva' => $totalTva,
            'total_ttc' => round($totalHt + $totalTva, 2),
            'notes' => $this->cleanText($data['notes'] ?? null),
        ];
    }

    /**
     * Keep only the known keys and compute each line total. A per-day line
     * with a period but no quantity gets the number of days in the period.
     *
     * @param  array<int, array<string, mixed>>  $lines
     * @return array<int, array<string, mixed>>
     */
    public function normalizeLines(array $lines): array
    {
        $clean = [];

        foreach (array_values($lines) as $index => $line) {
            $clean[] = $this->normalizeLine($line, $index);
        }

        return $clean;
    }

    /**
     * @param  array<string, mixed>  $line
     * @return array<string, mixed>
     */
    private function normalizeLine(array $line, int $index): array
    {
        $type = ($line['type'] ?? self::LINE_PER_DAY) === self::LINE_FLAT ? self::LINE_FLAT : self::LINE_PER_DAY;
        $start = $this->cleanDate($line['period_start'] ?? null);
        $end = $this->cleanDate($line['period_end'] ?? null);

        $clean = [
            'type' => $type,
            'designation' => trim((string) ($line['designation'] ?? '')),
            'description' => $this->cleanText($line['description'] ?? null),
            'period_start' => $start,
            'period_end' => $end,
            'quantity' => null,
            'unit_price' => null,
            'total' => 0.0,
        ];

        if ($type === self::LINE_FLAT) {
            $clean['total'] = round((float) ($line['total'] ?? 0), 2);

            return $clean;
        }

        $quantity = $line['quantity'] ?? null;

        if ($quantity === null || $quantity === '') {
            if (! $start || ! $end) {
                throw ValidationException::withMessages([
                    "lines.{$index}.quantity" => 'Indiquez la durée (jours) ou la période de location.',
                ]);
            }

            $quantity = max(1, (int) abs(Carbon::parse($start)->diffInDays(Carbon::parse($end))));
        }

        $clean['quantity'] = round((float) $quantity, 2);
        $clean['unit_price'] = round((float) ($line['unit_price'] ?? 0), 2);
        $clean['total'] = round($clean['quantity'] * $clean['unit_price'], 2);

        return $clean;
    }

    private function nextSequence(BillingDocumentType $type, int $year): int
    {
        $last = BillingDocument::query()
            ->where('type', $type->value)
            ->where('year', $year)
            ->lockForUpdate()
            ->max('sequence');

        return ((int) $last) + 1;
    }

    private function cleanText(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function cleanDate(mixed $value): ?string
    {
        return blank($value) ? null : Carbon::parse($value)->toDateString();
    }
}
