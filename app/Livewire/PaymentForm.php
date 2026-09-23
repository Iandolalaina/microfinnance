<?php

namespace App\Livewire;

use App\Models\Payment;
use App\Models\Schedule;
use App\Services\Mvola\MvolaServiceInterface;
use App\Services\PaymentConfirmationService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layout')]
class PaymentForm extends Component
{
    public Schedule $schedule;

    public string $mvolaPhone = '';

    public bool $paymentDone = false;

    public ?Payment $lastPayment = null;

    /**
     * mount() s'exécute une seule fois, quand la page se charge.
     * $schedule est injecté automatiquement par Laravel grâce au
     * "Route Model Binding" (voir la route dans web.php).
     */
    public function mount(Schedule $schedule): void
    {
        // Sécurité : on vérifie que cette échéance appartient bien
        // au client actuellement connecté, pas à quelqu'un d'autre.
        if ($schedule->loan->user_id !== auth()->id()) {
            abort(403, "Cette échéance ne vous appartient pas.");
        }

        $this->schedule = $schedule;
    }

    /**
     * Déclenchée quand le client clique sur "Confirmer le paiement".
     */
    public function pay(MvolaServiceInterface $mvola, PaymentConfirmationService $confirmation): void
    {
        $this->validate([
            'mvolaPhone' => ['required', 'regex:/^0[0-9]{9}$/'],
        ], [
            'mvolaPhone.required' => 'Le numéro Mvola est obligatoire.',
            'mvolaPhone.regex' => 'Le numéro doit contenir 10 chiffres (ex: 0343500003).',
        ]);

        $amountDue = $this->schedule->amount_due - $this->schedule->amount_paid;

        // 1. On initie la demande de paiement (simulateur pour l'instant,
        //    vraie API Mvola plus tard — le code ici ne changera pas)
        $response = $mvola->initiatePayment(
            msisdn: $this->mvolaPhone,
            amount: $amountDue,
            description: 'Échéance prêt #' . $this->schedule->loan_id,
        );

        // 2. On enregistre le paiement en base, statut "pending" au départ
        $payment = Payment::create([
            'loan_id' => $this->schedule->loan_id,
            'schedule_id' => $this->schedule->id,
            'user_id' => auth()->id(),
            'amount' => $amountDue,
            'method' => 'mvola',
            'mvola_transaction_id' => $response['serverCorrelationId'],
            'mvola_phone' => $this->mvolaPhone,
            'status' => 'pending',
        ]);

        // 3. Avec le VRAI Mvola, on s'arrêterait ici : la confirmation
        //    arriverait plus tard via le webhook (voir MvolaWebhookController).
        //    Avec notre simulateur, on confirme immédiatement pour pouvoir
        //    tester tout le flux sans attendre.
        $confirmedPayment = $confirmation->confirm($payment);

        $this->lastPayment = $confirmedPayment;
        $this->paymentDone = true;
    }

    public function render()
    {
        return view('livewire.payment-form');
    }
}
