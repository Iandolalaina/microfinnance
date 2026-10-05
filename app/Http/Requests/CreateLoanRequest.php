<?php

namespace App\Http\Requests;

use App\Models\LoanType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'agent'], true);
    }

    public function rules(): array
    {
        return self::rulesFor($this->integer('loan_type_id') ?: null);
    }

    public static function rulesFor(?int $loanTypeId): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'loan_type_id' => ['required', 'exists:loan_types,id'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'repayment_frequency' => ['required', Rule::in(LoanType::find($loanTypeId)?->allowed_frequencies ?? [])],
            'amount' => [
                'required',
                'numeric',
                'min:1000',
                function (string $attribute, mixed $value, \Closure $fail) use ($loanTypeId): void {
                    if ($loanTypeId === null || ! is_numeric($value)) {
                        return;
                    }

                    $maxAmount = LoanType::find($loanTypeId)?->effective_max_amount;

                    if ($maxAmount !== null && (float) $value > (float) $maxAmount) {
                        $fail('Le montant depasse le plafond de ce type de credit ('.number_format((float) $maxAmount, 0, ',', ' ').' Ar).');
                    }
                },
            ],
            'interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:36'],
            'grace_period_days' => ['required', 'integer', 'min:0', 'max:120'],
            'first_due_date' => ['required', 'date'],
        ];
    }
}
