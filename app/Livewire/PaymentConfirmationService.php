<?php

namespace App\Services;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PaymentConfirmationService
{
    /**
     * Confirme un versement : met à jour le paiement, l'échéance liée,
     * puis génère le reçu PDF.
     */
    public function confirm(Payment $payment): Payment
    {
        // 1. On marque le paiement comme confirmé
        $payment->status = 'confirmed';
        $payment->paid_at = now();

        // 2. On met à jour l'échéance concernée (si elle existe)
        if ($payment->schedule_id) {
            $schedule = $payment->schedule;
            $schedule->amount_paid += $payment->amount;

            $schedule->status = $schedule->amount_paid >= $schedule->amount_due
                ? 'paid'
                : 'partial';

            $schedule->save();
        }

        // 3. On génère le PDF du reçu et on enregistre son chemin
        $payment->receipt_path = $this->generateReceiptPdf($payment);

        $payment->save();

        return $payment;
    }

    /**
     * Génère un PDF de reçu à partir d'une vue Blade, et le stocke
     * dans storage/app/public/receipts/.
     */
    protected function generateReceiptPdf(Payment $payment): string
    {
        $payment->loadMissing(['client', 'loan', 'schedule']);

        $pdf = Pdf::loadView('pdf.receipt', [
            'payment' => $payment,
        ]);

        $filename = 'receipts/recu-' . $payment->id . '-' . now()->timestamp . '.pdf';

        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }
}
