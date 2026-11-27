<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
@php
    /** @var \App\Models\BillingDocument $document */
    $isQuote = $document->type === \App\Enums\BillingDocumentType::Quote;
    $currency = $company['currency'] ?? 'DH';
    $money = fn ($value) => number_format((float) $value, 2, ',', ' ').' '.$currency;
    $date = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('d/m/Y') : '';
    $days = function ($quantity) {
        $quantity = (float) $quantity;
        $label = floor($quantity) == $quantity ? (string) (int) $quantity : number_format($quantity, 1, ',', '');

        return $label.' '.($quantity >= 2 ? 'jours' : 'jour');
    };
    $rate = rtrim(rtrim(number_format((float) $document->tva_rate, 2, ',', ''), '0'), ',');
    $clientDetails = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $document->client_details))));
@endphp
<style>
    /* Bottom margin keeps body text clear of the fixed footer band; the top
       margin applies from page 2 on (the header pulls itself back up on page 1). */
    @page { margin: 28pt 0 46pt 0; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: Helvetica, Arial, sans-serif; font-size: 9.5pt; color: #1a1a1a; }
    table { border-collapse: collapse; width: 100%; }
    td, th { padding: 0; vertical-align: top; }

    .header { margin-top: -28pt; background: #000; border-bottom: 4pt solid #f28a2c; padding: 15pt 42pt 14pt 42pt; }
    .header td { vertical-align: middle; }
    .logo { width: 124pt; }
    .doc-title { color: #fff; font-size: 23pt; font-weight: bold; text-align: right; letter-spacing: 0.5pt; margin-bottom: 6pt; }
    .doc-meta { color: #fff; font-size: 9.5pt; text-align: right; line-height: 17pt; }

    .content { padding: 24pt 42pt 0 42pt; }
    .party-label { color: #f28a2c; font-size: 9.5pt; font-weight: bold; margin-bottom: 5pt; }
    .party-name { font-size: 11pt; font-weight: bold; margin-bottom: 7pt; }
    .party-line { color: #4d4d4d; font-size: 8.8pt; line-height: 12.8pt; }

    .items { margin-top: 18pt; }
    /* Every coloured band is ONE cell holding an inner .cols table: fills on
       adjacent cells leave hairline seams in most PDF viewers, and dompdf
       moves a single row to the next page whole instead of splitting an
       item's title from its sub-line. */
    .cols { table-layout: fixed; }
    .cols td { padding: 0 6.5pt; }
    .c1 { width: 47%; }
    .c2 { width: 13.5%; }
    .c3 { width: 15%; }
    .c4 { width: 24.5%; }
    .items .band { background: #000; padding: 8pt 0; }
    .items .band td { color: #fff; font-size: 9pt; font-weight: bold; }
    .items tr.item { page-break-inside: avoid; }
    .items .item-cell { border-bottom: 0.8pt solid #c8c8c8; }
    .items .item-cell.shaded { background: #f2f2f2; }
    .items .main td { padding-top: 14pt; padding-bottom: 14pt; font-size: 9.8pt; }
    .items .main.has-sub td { padding-bottom: 3pt; }
    .items .sub td { padding-bottom: 14pt; color: #555; font-size: 8.2pt; line-height: 11.5pt; }
    .designation { font-weight: bold; }
    .num { text-align: right; white-space: nowrap; }

    .summary { margin-top: 18pt; }
    .totals td { font-size: 9.8pt; padding: 3pt 6.5pt 6pt 9pt; }
    .totals td.grand { background: #f28a2c; padding: 0; }
    .totals .grand td { color: #111; font-size: 11pt; font-weight: bold; padding: 7pt 6.5pt 7pt 9pt; }
    .words-label { color: #555; font-style: italic; font-size: 8.8pt; margin-bottom: 5pt; }
    .words { font-weight: bold; font-size: 9pt; }

    .notes { margin-top: 26pt; }
    .notes-text { color: #4d4d4d; font-size: 8.8pt; line-height: 12.8pt; }

    .footer { position: fixed; left: 0; right: 0; bottom: -46pt; height: 46pt; background: #000; border-top: 3.5pt solid #f28a2c; color: #fff; text-align: center; font-size: 7.8pt; line-height: 13pt; padding-top: 8pt; }
</style>
</head>
<body>

<div class="footer">
    {{ $company['name'] }} - {{ $company['legal_form_short'] }} - {{ $company['address'] }}<br>
    RC : {{ $company['rc'] }} - ICE : {{ $company['ice'] }}
</div>

<div class="header">
    <table>
        <tr>
            <td style="width: 50%;">
                @if ($logo)
                    <img src="{{ $logo }}" class="logo" alt="">
                @else
                    <div class="doc-title" style="text-align: left;">{{ $company['name'] }}</div>
                @endif
            </td>
            <td style="width: 50%;">
                <div class="doc-title">{{ $document->type->title() }}</div>
                <div class="doc-meta">N° : {{ $document->number }}</div>
                <div class="doc-meta">Date : {{ $date($document->issue_date) }}</div>
                @if ($isQuote && $document->validity_days)
                    <div class="doc-meta">Validité : {{ $document->validity_days }} jours</div>
                @endif
            </td>
        </tr>
    </table>
</div>

<div class="content">
    <table>
        <tr>
            <td style="width: 52.5%; padding-right: 16pt;">
                <div class="party-label">ÉMETTEUR</div>
                <div class="party-name">{{ $company['name'] }}</div>
                <div class="party-line">{{ $company['activity'] }}</div>
                <div class="party-line">{{ $company['legal_form'] }}</div>
                <div class="party-line">Siège : {{ $company['address'] }}</div>
                <div class="party-line">RC : {{ $company['rc'] }}</div>
                <div class="party-line">ICE : {{ $company['ice'] }}</div>
            </td>
            <td style="width: 47.5%;">
                <div class="party-label">CLIENT</div>
                <div class="party-name">{{ mb_strtoupper($document->client_name) }}</div>
                @foreach ($clientDetails as $line)
                    <div class="party-line">{{ $line }}</div>
                @endforeach
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="band">
                    <table class="cols">
                        <tr>
                            <td class="c1">Désignation</td>
                            <td class="c2">Durée</td>
                            <td class="c3 num">Prix/jour HT</td>
                            <td class="c4 num">Total HT</td>
                        </tr>
                    </table>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->lines as $index => $line)
                @php
                    $perDay = ($line['type'] ?? 'per_day') === 'per_day';
                    $subLines = array_values(array_filter([
                        (! empty($line['period_start']) && ! empty($line['period_end']))
                            ? 'Du '.$date($line['period_start']).' au '.$date($line['period_end'])
                            : null,
                        $line['description'] ?? null,
                    ]));
                @endphp
                <tr class="item">
                    <td class="item-cell {{ $index % 2 === 0 ? 'shaded' : '' }}">
                        <table class="cols">
                            <tr class="main {{ $subLines ? 'has-sub' : '' }}">
                                <td class="c1 designation">{{ $line['designation'] }}</td>
                                <td class="c2">{{ $perDay ? $days($line['quantity'] ?? 1) : 'Forfait' }}</td>
                                <td class="c3 num">{{ $perDay ? $money($line['unit_price'] ?? 0) : '-' }}</td>
                                <td class="c4 num">{{ $money($line['total'] ?? 0) }}</td>
                            </tr>
                            @if ($subLines)
                                <tr class="sub">
                                    <td colspan="3">{!! collect($subLines)->map(fn ($text) => nl2br(e($text)))->implode('<br>') !!}</td>
                                    <td></td>
                                </tr>
                            @endif
                        </table>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td style="width: 52.3%; vertical-align: bottom; padding-right: 14pt;">
                <div class="words-label">{{ $document->type->closingPhrase() }}</div>
                <div class="words">{{ $amountInWords }} TTC</div>
            </td>
            <td style="width: 47.7%;">
                <table class="totals">
                    <tr>
                        <td>Total HT</td>
                        <td class="num">{{ $money($document->total_ht) }}</td>
                    </tr>
                    <tr>
                        <td>TVA ({{ $rate }}%)</td>
                        <td class="num">{{ $money($document->total_tva) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="grand">
                            <table>
                                <tr>
                                    <td>TOTAL TTC</td>
                                    <td class="num">{{ $money($document->total_ttc) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if ($document->notes)
        <div class="notes">
            <div class="party-label">REMARQUES</div>
            <div class="notes-text">{!! nl2br(e($document->notes)) !!}</div>
        </div>
    @endif
</div>

</body>
</html>
