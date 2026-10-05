<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateWebPushKeys extends Command
{
    protected $signature = 'webpush:generate-keys';

    protected $description = 'Génère et enregistre une paire de clés VAPID dans le fichier .env';

    public function handle(): int
    {
        $envPath = base_path('.env');
        $contents = is_file($envPath) ? file_get_contents($envPath) : false;

        if ($contents === false) {
            $this->error('Le fichier .env est introuvable.');

            return self::FAILURE;
        }

        if (preg_match('/^WEBPUSH_VAPID_PUBLIC_KEY=.+$/m', $contents)
            && preg_match('/^WEBPUSH_VAPID_PRIVATE_KEY=.+$/m', $contents)) {
            $this->info('Les clés VAPID sont déjà configurées. Elles ont été conservées.');

            return self::SUCCESS;
        }

        try {
            $keys = VAPID::createVapidKeys();
        } catch (\Throwable $exception) {
            $this->error('OpenSSL n’a pas pu générer les clés VAPID : '.$exception->getMessage());

            return self::FAILURE;
        }

        foreach ([
            'WEBPUSH_VAPID_PUBLIC_KEY' => $keys['publicKey'],
            'WEBPUSH_VAPID_PRIVATE_KEY' => $keys['privateKey'],
        ] as $name => $value) {
            $line = $name.'='.$value;
            $pattern = '/^'.preg_quote($name, '/').'=.*$/m';

            if (preg_match($pattern, $contents)) {
                $contents = preg_replace($pattern, $line, $contents);
            } else {
                $contents = rtrim($contents)."\n{$line}\n";
            }
        }

        if (file_put_contents($envPath, $contents, LOCK_EX) === false) {
            $this->error('Impossible d’enregistrer les clés dans .env.');

            return self::FAILURE;
        }

        $this->call('config:clear');
        $this->info('Clés VAPID générées et enregistrées dans .env. La clé privée n’a pas été affichée.');

        return self::SUCCESS;
    }
}