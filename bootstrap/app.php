<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // ← ligne ajoutée pour activer routes/api.php
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);

        // Fait confiance aux en-têtes envoyés par un proxy devant l'appli
        // (ngrok pendant les tests, et plus tard un vrai hébergeur).
        // Sans ça, Laravel peut mal détecter le HTTPS et bloquer certaines
        // requêtes de sécurité (CSRF) lors d'un accès via tunnel.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
