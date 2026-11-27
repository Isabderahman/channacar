<?php

namespace App\Enums;

enum BillingDocumentType: string
{
    case Quote = 'quote';
    case Invoice = 'invoice';

    /** Title printed in the PDF header. */
    public function title(): string
    {
        return match ($this) {
            self::Quote => 'DEVIS',
            self::Invoice => 'FACTURE',
        };
    }

    /** Number prefix: DEV-2026-0001 / FAC-2026-0001. */
    public function prefix(): string
    {
        return match ($this) {
            self::Quote => 'DEV',
            self::Invoice => 'FAC',
        };
    }

    /** "Arrêté le présent devis" / "Arrêtée la présente facture" (gender agreement). */
    public function closingPhrase(): string
    {
        return match ($this) {
            self::Quote => 'Arrêté le présent devis à la somme de :',
            self::Invoice => 'Arrêtée la présente facture à la somme de :',
        };
    }
}
