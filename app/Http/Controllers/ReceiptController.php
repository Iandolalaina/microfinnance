<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceiptController extends Controller
{
    /**
     * Télécharge le reçu PDF d'un paiement, uniquement si le paiement
     * appartient bien au client connecté.
     */
    public function download(Payment $payment): StreamedResponse
    {
        $user = auth()->user();

        $isOwner = $payment->user_id === $user->id;
        $isStaff = in_array($user->role, ['admin', 'agent'], true);

        if (! $isOwner && ! $isStaff) {
            abort(403, "Ce reçu ne vous appartient pas.");
        }

        if (! $payment->receipt_path || ! Storage::disk('public')->exists($payment->receipt_path)) {
            abort(404, "Reçu introuvable.");
        }

        return Storage::disk('public')->response($payment->receipt_path, 'recu-mitsinjo-' . $payment->id . '.pdf');
    }
}
