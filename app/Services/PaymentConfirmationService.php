<?php

namespace App\Services;

use App\Events\PaymentReceived;
use App\Models\Payment;
use App\Models\Schedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

class PaymentConfirmationService
{
    public function __construct(private LoanService $loanService) {}

    /**
     * Confirme un versement, applique son montant sur les echeances, genere
     * le recu PDF, puis declenche le SMS de confirmation via evenement.
     */
    public function confirm(Payment $payment): Payment
    {
        [$payment, $wasAlreadyConfirmed] = DB::transaction(function () use ($payment): array {
            $lockedPayment = Payment::whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPayment->status === 'confirmed' || $lockedPayment->status === 'SUCCESS') {
                return [$lockedPayment, true];
            }

            $lockedPayment->status = 'confirmed';
            $lockedPayment->paid_at = now();
            $lockedPayment->save();

            if ($lockedPayment->schedule_id) {
                $this->applyPaymentToSchedules($lockedPayment);
            } elseif ($lockedPayment->loan_schedule_id) {
                $this->loanService->applyPayment(
                    $lockedPayment->loanSchedule,
                    (float) $lockedPayment->amount
                );
            }

            return [$lockedPayment, false];
        });

        if ($wasAlreadyConfirmed) {
            return $payment->refresh();
        }

        $payment->receipt_path = $this->generateReceiptPdf($payment);
        $payment->save();

        Event::dispatch(new PaymentReceived($payment->refresh()));

        return $payment;
    }

    /**
     * Impute le montant paye sur l'echeance courante, puis sur les suivantes
     * lorsqu'il existe un surplus.
     */
    protected function applyPaymentToSchedules(Payment $payment): void
    {
        $currentSchedule = Schedule::whereKey($payment->schedule_id)
            ->lockForUpdate()
            ->firstOrFail();

        $schedules = Schedule::where('loan_id', $payment->loan_id)
            ->where(function ($query) use ($currentSchedule) {
                $query->where('due_date', '>', $currentSchedule->due_date)
                    ->orWhere(function ($query) use ($currentSchedule) {
                        $query->where('due_date', $currentSchedule->due_date)
                            ->where('id', '>=', $currentSchedule->id);
                    });
            })
            ->where('status', '!=', 'paid')
            ->orderBy('due_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $remainingPayment = (float) $payment->amount;

        foreach ($schedules as $schedule) {
            if ($remainingPayment <= 0) {
                break;
            }

            $remainingDue = max(0, (float) $schedule->amount_due - (float) $schedule->amount_paid);

            if ($remainingDue <= 0) {
                $schedule->status = 'paid';
                $schedule->save();

                continue;
            }

            $amountApplied = min($remainingPayment, $remainingDue);

            $schedule->amount_paid = (float) $schedule->amount_paid + $amountApplied;
            $remainingPayment -= $amountApplied;

            $schedule->status = (float) $schedule->amount_paid >= (float) $schedule->amount_due
                ? 'paid'
                : 'partial';

            $schedule->save();
        }
    }

    /**
     * Genere un PDF de recu a partir d'une vue Blade, et le stocke
     * dans storage/app/public/receipts/.
     */
    protected function generateReceiptPdf(Payment $payment): string
    {
        $payment->loadMissing(['client', 'loan', 'schedule', 'loanSchedule']);

        $pdf = Pdf::loadView('pdf.receipt', [
            'payment' => $payment,
        ]);

        $filename = 'receipts/recu-'.$payment->id.'-'.now()->timestamp.'.pdf';

        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }
}
