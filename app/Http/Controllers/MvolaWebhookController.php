<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentConfirmationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MvolaWebhookController extends Controller
{
    /**
     * Point d'entrée que MVOLA_CALLBACK_URL (voir .env) appellera
     * automatiquement pour confirmer une transaction réelle.
     *
     * NOTE PÉDAGOGIQUE : la structure exacte du JSON envoyé par Mvola
     * sera à ajuster une fois la vraie documentation sandbox consultée
     * (elle varie légèrement selon les versions de leur API). La logique
     * de fond (retrouver le paiement, appeler le service de confirmation)
     * restera la même.
     */
    public function handle(Request $request, PaymentConfirmationService $confirmation): JsonResponse
    {
        $transactionId = $request->input('serverCorrelationId')
            ?? $request->input('transactionReference');

        if (! $transactionId) {
            return response()->json(['error' => 'Identifiant de transaction manquant'], 422);
        }

        $payment = Payment::where('mvola_transaction_id', $transactionId)->first();

        if (! $payment) {
            return response()->json(['error' => 'Paiement introuvable'], 404);
        }

        // Sécurité anti-doublon : si déjà confirmé, on ne refait rien
        if ($payment->status === 'confirmed') {
            return response()->json(['message' => 'Déjà confirmé']);
        }

        $status = $request->input('status', 'completed');

        if ($status === 'completed' || $status === 'success') {
            $confirmation->confirm($payment);
        } else {
            $payment->update(['status' => 'failed']);
        }

        return response()->json(['message' => 'Notification traitée avec succès']);
    }
}
