<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'agent_id',
        'amount',
        'interest_rate',
        'duration_months',
        'status',
        'disbursed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'disbursed_at' => 'datetime',
        ];
    }

    /**
     * Le client titulaire de ce prêt.
     * Exemple d'utilisation : $loan->client->name
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * L'agent ONG en charge du suivi de ce prêt.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Toutes les échéances de remboursement de ce prêt.
     * Exemple d'utilisation : $loan->schedules
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Tous les versements liés à ce prêt (Mvola + espèces confondus).
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ------------------------------------------------------------
    // HELPERS UTILES POUR LE TABLEAU DE BORD CLIENT
    // ------------------------------------------------------------

    /**
     * Calcule le montant total déjà remboursé sur ce prêt.
     */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->schedules()->sum('amount_paid');
    }

    /**
     * Calcule le montant restant à payer sur ce prêt.
     */
    public function getRemainingAmountAttribute(): float
    {
        return (float) $this->schedules()->sum('amount_due') - $this->total_paid;
    }

    /**
     * Retourne la prochaine échéance non soldée.
     */
    public function nextSchedule(): ?Schedule
    {
        return $this->schedules()
            ->whereIn('status', ['pending', 'late', 'partial'])
            ->orderBy('due_date')
            ->first();
    }
}
