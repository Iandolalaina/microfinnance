<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Services\SmsNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPaymentReceivedSms implements ShouldQueue
{
    public bool $afterCommit = true;

    public function __construct(protected SmsNotifier $smsNotifier)
    {
    }

    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment->loadMissing('client');
        $client = $payment->client;

        if (! $client || ! $client->phone) {
            return;
        }

        $message = sprintf(
            'MITSINJO: Versement de %s Ar bien recu le %s. Merci !',
            number_format((float) $payment->amount, 0, ' ', ' '),
            $payment->paid_at->format('d/m/Y a H:i'),
        );

        $this->smsNotifier->send($client, $client->phone, $message, 'confirmation');
    }
}
