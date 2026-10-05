<?php

namespace App\Providers;

use App\Events\PaymentReceived;
use App\Listeners\SendPaymentReceivedSms;
use App\Services\Mvola\FakeMvolaService;
use App\Services\Mvola\MvolaServiceInterface;
use App\Services\Sms\FakeSmsService;
use App\Services\Sms\SmsServiceInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MvolaServiceInterface::class, FakeMvolaService::class);

        // Même principe que pour Mvola : aujourd'hui FakeSmsService (simulateur),
        // plus tard RealSmsService::class une fois la passerelle SMS choisie.
        $this->app->bind(SmsServiceInterface::class, FakeSmsService::class);
    }

    public function boot(): void
    {
        Event::listen(PaymentReceived::class, SendPaymentReceivedSms::class);

        if (! getenv('OPENSSL_CONF')) {
            $wampOpenSslConfig = dirname(PHP_BINARY).DIRECTORY_SEPARATOR.'extras'.DIRECTORY_SEPARATOR.'ssl'.DIRECTORY_SEPARATOR.'openssl.cnf';

            if (is_file($wampOpenSslConfig)) {
                putenv('OPENSSL_CONF='.$wampOpenSslConfig);
            }
        }

        \Illuminate\Support\Facades\Schema::defaultStringLength(191);
    }
}
