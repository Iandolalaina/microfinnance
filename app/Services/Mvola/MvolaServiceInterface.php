<?php

namespace App\Services\Mvola;

interface MvolaServiceInterface
{
    /**
     * Initie une demande de paiement Mvola.
     *
     * @param string $msisdn      Numéro Mvola du client (ex: 0343500003)
     * @param float  $amount      Montant à débiter
     * @param string $description Motif du paiement (ex: "Échéance prêt #12")
     *
     * @return array{status: string, serverCorrelationId: string}
     *         status = 'pending' juste après l'appel (Mvola répond toujours
     *         "en attente" immédiatement, la confirmation arrive plus tard
     *         via le webhook — c'est ce comportement qu'on reproduit ici).
     */
    public function initiatePayment(string $msisdn, float $amount, string $description): array;
}
