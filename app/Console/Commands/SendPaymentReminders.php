<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Models\SmsLog;
use App\Services\SmsNotifier;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    /**
     * Le nom qu'on tape après "php artisan" pour lancer cette commande.
     */
    protected $signature = 'reminders:send';

    protected $description = "Envoie un SMS de rappel aux clients dont l'échéance approche";

    public function handle(SmsNotifier $smsNotifier): int
    {
        $daysBefore = (int) config('sms.reminder_days_before');
        $targetDate = now()->addDays($daysBefore)->toDateString();

        // On cherche toutes les échéances NON payées dont la date tombe
        // exactement dans "X jours" à partir d'aujourd'hui.
        $schedules = Schedule::whereDate('due_date', $targetDate)
            ->where('status', 'pending')
            ->with('loan.client')
            ->get();

        $this->info("Échéances trouvées pour le {$targetDate} : {$schedules->count()}");

        foreach ($schedules as $schedule) {
            $client = $schedule->loan?->client;

            if (! $client || ! $client->phone) {
                continue;
            }

            // Sécurité anti-doublon simple : si ce client a déjà reçu un
            // rappel aujourd'hui, on ne le spam pas une seconde fois.
            $alreadySentToday = SmsLog::where('user_id', $client->id)
                ->where('type', 'reminder')
                ->whereDate('created_at', today())
                ->exists();

            if ($alreadySentToday) {
                continue;
            }

            $montant = $schedule->amount_due - $schedule->amount_paid;

            $message = sprintf(
                'MITSINJO: Bonjour %s, votre echeance de %s Ar arrive le %s. Merci de regulariser.',
                $client->name,
                number_format($montant, 0, ' ', ' '),
                $schedule->due_date->format('d/m/Y'),
            );

            $smsNotifier->send($client, $client->phone, $message, 'reminder');

            $this->line("Rappel envoyé à {$client->phone}");
        }

        return self::SUCCESS;
    }
}
