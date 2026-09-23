<?php

namespace App\Services\Mvola;

use Illuminate\Support\Str;

class FakeMvolaService implements MvolaServiceInterface
{
    /**
     * Simule l'appel à l'API Mvola : au lieu de faire une vraie requête HTTP,
     * on génère juste un faux identifiant de transaction et on répond "pending",
     * exactement comme le ferait la vraie API Mvola au premier appel.
     */
    public function initiatePayment(string $msisdn, float $amount, string $description): array
    {
        // En situation réelle, Mvola validerait le numéro, le solde, etc.
        // Ici on simule simplement un succès systématique.
        return [
            'status' => 'pending',
            'serverCorrelationId' => 'FAKE-' . Str::upper(Str::random(12)),
        ];
    }
}
