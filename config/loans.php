<?php

return [
    // Les taux sont provisoires jusqu'a leur validation formelle par VAHATRA.
    // Le champ default_interest_rate de loan_types reste la valeur de reference modifiable.
    'rates_are_provisional' => true,
    'interest_rate_period' => 'month',
    'default_interest_rates' => [
        'AGR' => env('LOAN_RATE_AGR') !== null ? (float) env('LOAN_RATE_AGR') : null,
        'AGRI_ELEVAGE' => env('LOAN_RATE_AGRI_ELEVAGE') !== null ? (float) env('LOAN_RATE_AGRI_ELEVAGE') : null,
        'SOCIAL_URGENCE' => env('LOAN_RATE_SOCIAL_URGENCE') !== null ? (float) env('LOAN_RATE_SOCIAL_URGENCE') : null,
    ],
    'max_amounts' => [
        'SOCIAL_URGENCE' => env('LOAN_SOCIAL_URGENCE_MAX_AMOUNT') !== null
            ? (float) env('LOAN_SOCIAL_URGENCE_MAX_AMOUNT')
            : null,
    ],
];
