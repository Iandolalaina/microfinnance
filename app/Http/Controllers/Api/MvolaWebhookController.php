<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentConfirmationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MvolaWebhookController extends Controller
{
    /**
     * Point d'entree que MVOLA_CALLBACK_URL appellera automatiquement pour
     * confirmer une transaction reelle.
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

        if ($payment->status === 'confirmed' || $payment->status === 'SUCCESS') {
            return response()->json(['message' => 'Deja confirme']);
        }

        $status = Str::upper((string) $request->input('status', 'SUCCESS'));

        if (in_array($status, ['COMPLETED', 'SUCCESS'], true)) {
            $confirmation->confirm($payment);
        } else {
            $payment->update(['status' => 'failed']);
        }

        return response()->json(['message' => 'Notification traitee avec succes']);
    }
}
