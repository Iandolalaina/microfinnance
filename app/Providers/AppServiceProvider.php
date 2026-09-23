<?php

namespace App\Providers;

use App\Services\Mvola\FakeMvolaService;
use App\Services\Mvola\MvolaServiceInterface;
use App\Services\Sms\FakeSmsService;
use App\Services\Sms\SmsServiceInterface;
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
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);
    }
}
