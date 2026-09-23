<?php

namespace App\Livewire;

use App\Models\Payment;
use App\Models\Schedule;
use App\Services\PaymentConfirmationService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layout')]
class ManualPayment extends Component
{
    public Schedule $schedule;

    public string $amount = '';

    public bool $done = false;

    public ?Payment $lastPayment = null;

    public function mount(Schedule $schedule): void
    {
        $this->schedule = $schedule;

        // Pré-remplit le montant avec ce qu'il reste à payer, l'agent
        // peut l'ajuster (ex: le client n'a payé qu'une partie en espèces)
        $this->amount = (string) ($schedule->amount_due - $schedule->amount_paid);
    }

    public function record(PaymentConfirmationService $confirmation): void
    {
        $this->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ], [
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.min' => 'Le montant doit être supérieur à 0.',
        ]);

        // Contrairement au paiement Mvola, un versement en espèces est
        // considéré confirmé IMMÉDIATEMENT : l'agent a l'argent en main,
        // il n'y a pas de "webhook" à attendre.
        $payment = Payment::create([
            'loan_id' => $this->schedule->loan_id,
            'schedule_id' => $this->schedule->id,
            'user_id' => $this->schedule->loan->user_id,
            'agent_id' => auth()->id(),
            'amount' => (float) $this->amount,
            'method' => 'cash',
            'status' => 'pending', // sera passé à "confirmed" juste après
        ]);

        $this->lastPayment = $confirmation->confirm($payment);
        $this->done = true;
    }

    public function render()
    {
        return view('livewire.manual-payment');
    }
}
