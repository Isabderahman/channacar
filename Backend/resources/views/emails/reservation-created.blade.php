<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle réservation</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:Arial,Helvetica,sans-serif;color:#18181b;">
    <div style="max-width:600px;margin:0 auto;padding:24px;">
        <h1 style="font-size:20px;margin:0 0 16px;">Nouvelle réservation reçue</h1>
        <p style="margin:0 0 16px;color:#52525b;">
            Une nouvelle réservation vient d'être enregistrée sur le site.
        </p>

        <table style="width:100%;border-collapse:collapse;background:#ffffff;border-radius:8px;overflow:hidden;">
            <tbody>
                <tr>
                    <td style="padding:10px 14px;border-bottom:1px solid #e4e4e7;font-weight:bold;">Numéro</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #e4e4e7;">{{ $reservation->reservation_number }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 14px;border-bottom:1px solid #e4e4e7;font-weight:bold;">Client</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #e4e4e7;">{{ optional($reservation->client)->full_name }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 14px;border-bottom:1px solid #e4e4e7;font-weight:bold;">Téléphone</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #e4e4e7;">{{ optional($reservation->client)->phone }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 14px;border-bottom:1px solid #e4e4e7;font-weight:bold;">Email</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #e4e4e7;">{{ optional($reservation->client)->email ?: '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 14px;border-bottom:1px solid #e4e4e7;font-weight:bold;">Voiture</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #e4e4e7;">{{ trim(optional($reservation->car)->brand . ' ' . optional($reservation->car)->name) }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 14px;border-bottom:1px solid #e4e4e7;font-weight:bold;">Prise en charge</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #e4e4e7;">
                        {{ $reservation->pickup_date?->format('d/m/Y') }} {{ substr((string) $reservation->pickup_time, 0, 5) }}
                        @if($reservation->pickupLocation) — {{ $reservation->pickupLocation->name }} @endif
                    </td>
                </tr>
                <tr>
                    <td style="padding:10px 14px;border-bottom:1px solid #e4e4e7;font-weight:bold;">Restitution</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #e4e4e7;">
                        {{ $reservation->dropoff_date?->format('d/m/Y') }} {{ substr((string) $reservation->dropoff_time, 0, 5) }}
                        @if($reservation->dropoffLocation) — {{ $reservation->dropoffLocation->name }} @endif
                    </td>
                </tr>
                @if($reservation->extras && $reservation->extras->count())
                <tr>
                    <td style="padding:10px 14px;border-bottom:1px solid #e4e4e7;font-weight:bold;">Extras</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #e4e4e7;">{{ $reservation->extras->pluck('name')->join(', ') }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding:10px 14px;font-weight:bold;">Total</td>
                    <td style="padding:10px 14px;">{{ $reservation->total_price }} MAD</td>
                </tr>
            </tbody>
        </table>

        @if($reservation->notes)
        <p style="margin:16px 0 0;color:#52525b;"><strong>Notes :</strong> {{ $reservation->notes }}</p>
        @endif
    </div>
</body>
</html>
