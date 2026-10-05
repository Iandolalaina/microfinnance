<?php

namespace App\Livewire;

use App\Models\LoanSchedule;
use App\Models\Payment;
use App\Services\Mvola\MvolaServiceInterface;
use App\Services\PaymentConfirmationService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layout')]
class LoanSchedulePaymentForm extends Component
{
    public LoanSchedule $schedule;

    public string $mvolaPhone = '';

    public bool $paymentDone = false;

    public ?Payment $lastPayment = null;

    public function mount(LoanSchedule $schedule): void
    {
        if ($schedule->loan->user_id !== auth()->id()) {
            abort(403, 'Cette echeance ne vous appartient pas.');
        }

        if ($schedule->status === 'paid') {
            abort(409, 'Cette echeance est deja reglee.');
        }

        $this->schedule = $schedule;
    }

    public function pay(MvolaServiceInterface $mvola, PaymentConfirmationService $confirmation): void
    {
        $this->validate([
            'mvolaPhone' => ['required', 'regex:/^0[0-9]{9}$/'],
        ], [
            'mvolaPhone.required' => 'Le numero Mvola est obligatoire.',
            'mvolaPhone.regex' => 'Le numero doit contenir 10 chiffres (ex: 0343500003).',
        ]);

        $amountDue = round((float) $this->schedule->amount_due - (float) $this->schedule->amount_paid, 2);

        if ($amountDue <= 0) {
            abort(409, 'Cette echeance est deja reglee.');
        }

        $response = $mvola->initiatePayment(
            msisdn: $this->mvolaPhone,
            amount: $amountDue,
            description: 'Echeance pret #'.$this->schedule->loan_id,
        );

        $payment = Payment::create([
            'loan_id' => $this->schedule->loan_id,
            'loan_schedule_id' => $this->schedule->id,
            'user_id' => auth()->id(),
            'amount' => $amountDue,
            'method' => 'mvola',
            'mvola_transaction_id' => $response['serverCorrelationId'],
            'mvola_phone' => $this->mvolaPhone,
            'status' => 'pending',
        ]);

        $this->lastPayment = $confirmation->confirm($payment);
        $this->paymentDone = true;
    }

    public function render()
    {
        return view('livewire.payment-form');
    }
}
