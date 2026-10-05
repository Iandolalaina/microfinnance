<?php

return [
    'vapid' => [
        'subject' => env('WEBPUSH_VAPID_SUBJECT', 'mailto:contact@example.org'),
        'public_key' => env('WEBPUSH_VAPID_PUBLIC_KEY'),
        'private_key' => env('WEBPUSH_VAPID_PRIVATE_KEY'),
    ],
];