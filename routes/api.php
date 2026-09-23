<?php

use App\Http\Controllers\Api\MvolaWebhookController;
use Illuminate\Support\Facades\Route;

// Cette route correspond à MVOLA_CALLBACK_URL dans le .env
// Elle est en dehors du middleware "auth" car c'est Mvola (un serveur externe)
// qui l'appelle, pas un utilisateur connecté.
Route::post('/webhooks/mvola', [MvolaWebhookController::class, 'handle']);
