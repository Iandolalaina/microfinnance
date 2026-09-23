<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table "schedules" : l'échéancier de remboursement d'un prêt.
     * C'est CETTE table que le client va payer, échéance par échéance,
     * via Mvola. Elle sert aussi de base au Scheduler pour les rappels SMS.
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('loan_id')
                  ->constrained('loans')
                  ->cascadeOnDelete();

            $table->date('due_date');                    // Date d'échéance
            $table->decimal('amount_due', 12, 2);         // Montant attendu pour cette échéance
            $table->decimal('amount_paid', 12, 2)->default(0); // Montant réellement payé (cumulé)

            $table->enum('status', ['pending', 'paid', 'late', 'partial'])
                  ->default('pending');
            // pending = pas encore due/payée, paid = soldée,
            // late = en retard, partial = payée partiellement

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
