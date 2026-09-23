<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table "loans" : un microcrédit accordé à un client.
     * Chaque prêt donnera naissance à plusieurs échéances (table "schedules").
     */
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();

            // Le client qui a contracté le prêt
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete(); // si le client est supprimé, ses prêts le sont aussi

            // L'agent ONG qui a validé/suivi le dossier (facultatif)
            $table->foreignId('agent_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->decimal('amount', 12, 2);          // Montant du crédit (ex: 1 500 000,00 Ar)
            $table->decimal('interest_rate', 5, 2)->default(0); // Taux d'intérêt en % (ex: 5.00)
            $table->unsignedInteger('duration_months'); // Durée du prêt en mois

            $table->enum('status', ['pending', 'active', 'completed', 'defaulted'])
                  ->default('pending');
            // pending = en attente de validation, active = en cours,
            // completed = totalement remboursé, defaulted = en défaut de paiement

            $table->dateTime('disbursed_at')->nullable(); // Date de déblocage des fonds

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
