<?php

return [
    // Nombre de jours avant l'échéance auquel envoyer le rappel automatique
    'reminder_days_before' => env('SMS_RAPPEL_JOURS_AVANT', 3),

    // Paramètres du futur vrai fournisseur SMS (pas encore utilisés par le simulateur)
    'gateway_url' => env('SMS_GATEWAY_URL'),
    'api_key' => env('SMS_GATEWAY_API_KEY'),
    'sender_id' => env('SMS_SENDER_ID', 'MITSINJO'),
];
