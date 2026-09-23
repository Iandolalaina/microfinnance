<?php

namespace App\Services;

use App\Models\SmsLog;
use App\Models\User;
use App\Services\Sms\SmsServiceInterface;
use Throwable;

class SmsNotifier
{
    public function __construct(protected SmsServiceInterface $smsService)
    {
    }

    /**
     * Envoie un SMS ET trace l'opération dans sms_logs, que l'envoi
     * réussisse ou échoue.
     *
     * @param User|null $user Peut être null si on envoie à un numéro
     *                        qui n'est pas forcément lié à un compte.
     * @param string $type    'reminder' | 'confirmation' | 'announcement' | 'other'
     */
    public function send(?User $user, string $phone, string $message, string $type = 'other'): SmsLog
    {
        $log = SmsLog::create([
            'user_id' => $user?->id,
            'phone' => $phone,
            'message' => $message,
            'type' => $type,
            'status' => 'pending',
        ]);

        try {
            $success = $this->smsService->send($phone, $message);

            $log->update([
                'status' => $success ? 'sent' : 'failed',
                'sent_at' => $success ? now() : null,
            ]);
        } catch (Throwable $e) {
            // Si la passerelle SMS plante (panne réseau, clé API invalide...),
            // on trace l'erreur au lieu de faire planter toute l'application.
            $log->update([
                'status' => 'failed',
                'provider_response' => $e->getMessage(),
            ]);
        }

        return $log;
    }
}
