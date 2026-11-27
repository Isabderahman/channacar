<?php

/*
| Legal identity of the agency, printed on devis and factures (émetteur block
| and footer). Change it here, not in the PDF template.
*/

return [
    'name' => 'CHANÂA CAR',
    'activity' => 'Location de véhicules automobiles sans chauffeur',
    'legal_form' => 'SARL à associé unique',
    'legal_form_short' => 'SARL AU',
    'address' => 'N 289 Lot Azli Sud, Marrakech',
    'rc' => '144103',
    'ice' => '003420487000066',
    'currency' => 'DH',
    // Defaults for new documents.
    'default_tva_rate' => 20,
    'default_quote_validity_days' => 15,
];
