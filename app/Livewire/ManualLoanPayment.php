<?php

namespace App\Livewire;

use App\Models\LoanSchedule;
use App\Models\Payment;
use App\Services\PaymentConfirmationService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layout')]
class ManualLoanPayment extends Component
{
    public LoanSchedule $schedule;

    public string $amount = '';

    public bool $done = false;

    public ?Payment $lastPayment = null;

    public function mount(LoanSchedule $schedule): void
    {
        if (auth()->user()->role === 'agent' && auth()->user()->zone_id !== $schedule->loan->client->zone_id) {
            abort(403);
        }

        if ($schedule->status === 'paid') {
            abort(409, 'Cette echeance est deja reglee.');
        }

        $this->schedule = $schedule;
        $this->amount = (string) round((float) $schedule->amount_due - (float) $schedule->amount_paid, 2);
    }

    public function record(PaymentConfirmationService $confirmation): void
    {
        $remaining = round((float) $this->schedule->amount_due - (float) $this->schedule->amount_paid, 2);

        $this->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.$remaining],
        ], [
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit etre un nombre.',
            'amount.min' => 'Le montant doit etre superieur a 0.',
            'amount.max' => 'Le montant ne peut pas depasser le solde de cette echeance.',
        ]);

        $payment = Payment::create([
            'loan_id' => $this->schedule->loan_id,
            'loan_schedule_id' => $this->schedule->id,
            'user_id' => $this->schedule->loan->user_id,
            'agent_id' => auth()->id(),
            'amount' => (float) $this->amount,
            'method' => 'cash',
            'status' => 'pending',
        ]);

        $this->lastPayment = $confirmation->confirm($payment);
        $this->done = true;
    }

    public function render()
    {
        return view('livewire.manual-payment');
    }
}
