<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

class FakeSmsService implements SmsServiceInterface
{
    /**
     * Simule l'envoi : au lieu d'appeler une vraie passerelle SMS,
     * on écrit simplement le message dans storage/logs/laravel.log
     * et on répond "succès" systématiquement.
     */
    public function send(string $phone, string $message): bool
    {
        Log::info("[SMS SIMULÉ] Vers {$phone} : {$message}");

        return true;
    }
}
