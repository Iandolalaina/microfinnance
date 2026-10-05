<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'min_duration_months',
        'max_duration_months',
        'allowed_frequencies',
        'allows_grace_period',
        'default_interest_rate',
        'max_amount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'allowed_frequencies' => 'array',
            'allows_grace_period' => 'boolean',
            'default_interest_rate' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function getEffectiveMaxAmountAttribute(): ?float
    {
        $configuredLimit = config('loans.max_amounts.'.$this->code);
        $maxAmount = $this->max_amount ?? $configuredLimit;

        return $maxAmount === null ? null : (float) $maxAmount;
    }

    public function getEffectiveDefaultInterestRateAttribute(): float
    {
        $configuredRate = config('loans.default_interest_rates.'.$this->code);

        return (float) ($configuredRate ?? $this->default_interest_rate);
    }
}
