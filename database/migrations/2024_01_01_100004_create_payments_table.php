<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table "payments" : trace chaque versement réel, qu'il vienne
     * de Mvola (webhook automatique) ou d'une saisie manuelle par un agent
     * (versement en espèces sur le terrain).
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('loan_id')
                  ->constrained('loans')
                  ->cascadeOnDelete();

            // Échéance concernée (peut être nulle le temps que le rapprochement soit fait)
            $table->foreignId('schedule_id')
                  ->nullable()
                  ->constrained('schedules')
                  ->nullOnDelete();

            // Le client qui paie
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // L'agent qui a encaissé (uniquement pour un paiement en espèces)
            $table->foreignId('agent_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->decimal('amount', 12, 2);

            $table->enum('method', ['mvola', 'cash', 'other'])->default('mvola');

            // Identifiant unique renvoyé par Mvola : sert à éviter les doublons de webhook
            $table->string('mvola_transaction_id')->nullable()->unique();
            $table->string('mvola_phone', 20)->nullable(); // numéro Mvola utilisé pour payer

            $table->enum('status', ['pending', 'confirmed', 'failed'])->default('pending');

            $table->string('receipt_path')->nullable(); // chemin du PDF généré (reçu)

            $table->dateTime('paid_at')->nullable(); // date/heure réelle de la transaction

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
