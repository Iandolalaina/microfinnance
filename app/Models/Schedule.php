<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'due_date',
        'amount_due',
        'amount_paid',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount_due' => 'decimal:2',
            'amount_paid' => 'decimal:2',
        ];
    }

    /**
     * Le prêt auquel appartient cette échéance.
     * Exemple d'utilisation : $schedule->loan->client->name
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * Les versements effectués pour régler cette échéance précise.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Montant qu'il reste à payer sur cette échéance précise.
     */
    public function getRemainingAttribute(): float
    {
        return (float) $this->amount_due - (float) $this->amount_paid;
    }

    /**
     * Statut "réel" de l'échéance, calculé à la volée : si elle est encore
     * "pending" en base mais que sa date est dépassée, on la considère
     * "late" pour l'affichage, SANS avoir besoin d'un job qui tourne
     * en tâche de fond pour mettre à jour la base à chaque instant.
     */
    public function getEffectiveStatusAttribute(): string
    {
        if ($this->status === 'pending' && $this->due_date->isPast()) {
            return 'late';
        }

        return $this->status;
    }
}
