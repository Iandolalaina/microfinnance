<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'installment_number',
        'due_date',
        'principal_due',
        'interest_due',
        'amount_due',
        'amount_paid',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'principal_due' => 'decimal:2',
            'interest_due' => 'decimal:2',
            'amount_due' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'loan_schedule_id');
    }

    public function getRemainingAttribute(): float
    {
        return max(0, (float) $this->amount_due - (float) $this->amount_paid);
    }

    public function getEffectiveStatusAttribute(): string
    {
        if (in_array($this->status, ['pending', 'partial'], true) && $this->due_date->isBefore(today())) {
            return 'late';
        }

        return $this->status;
    }
}
