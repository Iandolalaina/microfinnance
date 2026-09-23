<?php

namespace App\Services;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PaymentConfirmationService
{
    public function __construct(protected SmsNotifier $smsNotifier)
    {
    }

    /**
     * Confirme un versement : met à jour le paiement, l'échéance liée,
     * génère le reçu PDF, puis envoie un SMS de confirmation au client.
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

        // 4. On envoie un SMS de confirmation au client
        $this->sendConfirmationSms($payment);

        return $payment;
    }

    /**
     * Envoie un SMS confirmant le versement — utile en particulier pour
     * les clients sans connexion internet (voir cahier des charges).
     */
    protected function sendConfirmationSms(Payment $payment): void
    {
        $client = $payment->client;

        if (! $client || ! $client->phone) {
            return;
        }

        $message = sprintf(
            'MITSINJO: Versement de %s Ar bien recu le %s. Merci !',
            number_format($payment->amount, 0, ' ', ' '),
            $payment->paid_at->format('d/m/Y a H:i'),
        );

        $this->smsNotifier->send($client, $client->phone, $message, 'confirmation');
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
