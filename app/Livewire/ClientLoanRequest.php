<?php

namespace App\Livewire;

use App\Models\Group;
use App\Models\Loan;
use App\Models\LoanType;
use App\Services\LoanService;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layout')]
class ClientLoanRequest extends Component
{
    public ?int $groupId = null;

    public ?int $loanTypeId = null;

    public string $purpose = '';

    public string $amount = '';

    public int $durationMonths = 3;

    public string $repaymentFrequency = 'monthly';

    public int $gracePeriodDays = 0;

    public bool $submitted = false;

    public function updatedLoanTypeId(): void
    {
        $type = LoanType::find($this->loanTypeId);

        if (! $type) {
            return;
        }

        $this->durationMonths = $type->min_duration_months;
        $this->repaymentFrequency = $type->allowed_frequencies[0] ?? 'monthly';

        if (! $type->allows_grace_period) {
            $this->gracePeriodDays = 0;
        }
    }

    public function submit(LoanService $loanService): void
    {
        $client = auth()->user();
        $validated = Validator::make([
            'user_id' => $client->id,
            'group_id' => $this->groupId,
            'loan_type_id' => $this->loanTypeId,
            'purpose' => $this->purpose,
            'amount' => $this->amount,
            'duration_months' => $this->durationMonths,
            'repayment_frequency' => $this->repaymentFrequency,
            'grace_period_days' => $this->gracePeriodDays,
        ], [
            'user_id' => ['required', 'exists:users,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'loan_type_id' => ['required', 'exists:loan_types,id'],
            'purpose' => ['required', 'string', 'max:255'],
            'amount' => [
                'required',
                'numeric',
                'min:1000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $limit = LoanType::find($this->loanTypeId)?->effective_max_amount;

                    if ($limit !== null && is_numeric($value) && (float) $value > $limit) {
                        $fail('Le montant demandé dépasse le plafond autorisé.');
                    }
                },
            ],
            'duration_months' => ['required', 'integer', 'min:1', 'max:12'],
            'repayment_frequency' => ['required', 'in:weekly,biweekly,monthly'],
            'grace_period_days' => ['required', 'integer', 'min:0', 'max:120'],
        ], [], [
            'loan_type_id' => 'type de crédit',
            'group_id' => 'groupe solidaire',
            'duration_months' => 'durée',
            'repayment_frequency' => 'fréquence',
            'grace_period_days' => 'période de grâce',
        ])->validate();

        $loanService->submitRequest($client, $validated);
        $this->submitted = true;
        $this->reset(['groupId', 'purpose', 'amount']);
    }

    public function render()
    {
        return view('livewire.client-loan-request', [
            'loanTypes' => LoanType::where('is_active', true)->orderBy('name')->get(),
            'selectedType' => LoanType::find($this->loanTypeId),
            'groups' => Group::where('status', 'active')
                ->whereHas('members', fn ($query) => $query->whereKey(auth()->id()))
                ->withCount([
                    'members as members_count' => fn ($query) => $query
                        ->where('users.role', 'client')
                        ->where('users.is_active', true),
                ])
                ->orderBy('name')
                ->get()
                ->filter(fn (Group $group) => $group->members_count >= 3 && $group->members_count <= 5)
                ->values(),
            'pendingRequest' => Loan::where('user_id', auth()->id())
                ->where('status', 'pending')
                ->latest()
                ->first(),
        ]);
    }
}
