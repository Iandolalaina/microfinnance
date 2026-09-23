<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table "sms_logs" : historique de TOUS les SMS envoyés
     * (rappels d'échéance, confirmations de paiement, annonces).
     * Indispensable pour le suivi/débogage de la passerelle SMS.
     */
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();

            // Peut être nul si le SMS est envoyé à un numéro non enregistré
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->string('phone', 20); // numéro réellement utilisé pour l'envoi
            $table->text('message');

            $table->enum('type', ['reminder', 'confirmation', 'announcement', 'other'])
                  ->default('other');

            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');

            $table->text('provider_response')->nullable(); // réponse brute de l'API SMS (debug)

            $table->dateTime('sent_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
