<?php

namespace App\Services\Sms;

interface SmsServiceInterface
{
    /**
     * Envoie un SMS brut à un numéro donné.
     * Retourne true si l'envoi a réussi, false sinon.
     */
    public function send(string $phone, string $message): bool;
}
