<?php

namespace App\Models;

use App\Enums\BillingDocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A devis (quote) or facture (invoice). Created and recalculated through
 * App\Services\BillingDocumentService, never filled directly from a request.
 *
 * Each entry of `lines` is:
 *   type          'per_day' (Durée = N jours × Prix/jour) or 'flat' (Forfait)
 *   designation   bold title ("Location Volkswagen Touareg Executive")
 *   description   optional grey sub-line
 *   period_start  optional Y-m-d, printed as "Du … au …"
 *   period_end    optional Y-m-d
 *   quantity      days (per_day only)
 *   unit_price    price per day HT (per_day only)
 *   total         line total HT
 */
class BillingDocument extends Model
{
    protected $fillable = [
        'type',
        'number',
        'year',
        'sequence',
        'issue_date',
        'validity_days',
        'client_name',
        'client_details',
        'lines',
        'tva_rate',
        'total_ht',
        'total_tva',
        'total_ttc',
        'notes',
        'source_document_id',
    ];

    protected $casts = [
        'type' => BillingDocumentType::class,
        'year' => 'integer',
        'sequence' => 'integer',
        'issue_date' => 'date:Y-m-d',
        'validity_days' => 'integer',
        'lines' => 'array',
        'tva_rate' => 'decimal:2',
        'total_ht' => 'decimal:2',
        'total_tva' => 'decimal:2',
        'total_ttc' => 'decimal:2',
    ];

    /** For a facture: the devis it was converted from. */
    public function source(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_document_id');
    }

    /** For a devis: the facture it was converted into, if any. */
    public function invoice(): HasOne
    {
        return $this->hasOne(self::class, 'source_document_id');
    }
}
