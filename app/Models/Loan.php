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
        'reviewed_by',
        'loan_type_id',
        'group_id',
        'solidarity_type',
        'amount',
        'interest_rate',
        'total_repayable',
        'purpose',
        'contract_number',
        'duration_months',
        'repayment_frequency',
        'grace_period_days',
        'first_due_date',
        'status',
        'disbursed_at',
        'approved_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'total_repayable' => 'decimal:2',
            'first_due_date' => 'date',
            'disbursed_at' => 'datetime',
            'approved_at' => 'datetime',
            'reviewed_at' => 'datetime',
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(LoanType::class, 'loan_type_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Toutes les échéances de remboursement de ce prêt.
     * Exemple d'utilisation : $loan->schedules
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function loanSchedules(): HasMany
    {
        return $this->hasMany(LoanSchedule::class);
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
        if ($this->loanSchedules()->exists()) {
            return (float) $this->loanSchedules()->sum('amount_paid');
        }

        return (float) $this->schedules()->sum('amount_paid');
    }

    /**
     * Calcule le montant restant à payer sur ce prêt.
     */
    public function getRemainingAmountAttribute(): float
    {
        if ($this->loanSchedules()->exists()) {
            return (float) $this->loanSchedules()->sum('amount_due') - $this->total_paid;
        }

        return (float) $this->schedules()->sum('amount_due') - $this->total_paid;
    }

    /**
     * Retourne la prochaine échéance non soldée.
     */
    public function nextSchedule(): Schedule|LoanSchedule|null
    {
        if ($this->loanSchedules()->exists()) {
            return $this->nextLoanSchedule();
        }

        return $this->schedules()
            ->whereIn('status', ['pending', 'late', 'partial'])
            ->orderBy('due_date')
            ->first();
    }

    public function nextLoanSchedule(): ?LoanSchedule
    {
        return $this->loanSchedules()
            ->whereIn('status', ['pending', 'late', 'partial'])
            ->orderBy('due_date')
            ->first();
    }
}
