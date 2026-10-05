<?php

namespace App\Livewire;

use App\Http\Requests\CreateLoanRequest;
use App\Models\Group;
use App\Models\LoanType;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layout')]
class LoanCreate extends Component
{
    public ?int $userId = null;

    public ?int $groupId = null;

    public ?int $loanTypeId = null;

    public string $purpose = '';

    public string $amount = '100000';

    public string $interestRate = '5';

    public int $durationMonths = 3;

    public string $repaymentFrequency = 'monthly';

    public int $gracePeriodDays = 0;

    public string $firstDueDate = '';

    public bool $created = false;

    public ?string $createdContractNumber = null;

    public function mount(): void
    {
        $this->firstDueDate = now()->addMonth()->toDateString();
    }

    public function updatedLoanTypeId(): void
    {
        $type = LoanType::find($this->loanTypeId);

        if (! $type) {
            return;
        }

        $this->interestRate = (string) $type->effective_default_interest_rate;
        $this->durationMonths = max($type->min_duration_months, min($this->durationMonths, $type->max_duration_months));
        $this->repaymentFrequency = $type->allowed_frequencies[0] ?? 'monthly';

        if (! $type->allows_grace_period) {
            $this->gracePeriodDays = 0;
        }
    }

    public function create(LoanService $loanService): void
    {
        $validated = Validator::make([
            'user_id' => $this->userId,
            'group_id' => $this->groupId,
            'loan_type_id' => $this->loanTypeId,
            'purpose' => $this->purpose,
            'amount' => $this->amount,
            'interest_rate' => $this->interestRate,
            'duration_months' => $this->durationMonths,
            'repayment_frequency' => $this->repaymentFrequency,
            'grace_period_days' => $this->gracePeriodDays,
            'first_due_date' => $this->firstDueDate,
        ], CreateLoanRequest::rulesFor($this->loanTypeId), [], [
            'user_id' => 'membre emprunteur',
            'group_id' => 'groupe solidaire',
            'loan_type_id' => 'type de crédit',
            'interest_rate' => 'taux',
            'duration_months' => 'durée',
            'repayment_frequency' => 'fréquence',
            'grace_period_days' => 'période de grâce',
            'first_due_date' => 'première échéance',
        ])->validate();

        $loan = $loanService->createLoan([
            'user_id' => $validated['user_id'],
            'group_id' => $validated['group_id'],
            'loan_type_id' => $validated['loan_type_id'],
            'purpose' => $validated['purpose'],
            'amount' => $validated['amount'],
            'interest_rate' => $validated['interest_rate'],
            'duration_months' => $validated['duration_months'],
            'repayment_frequency' => $validated['repayment_frequency'],
            'grace_period_days' => $validated['grace_period_days'],
            'first_due_date' => $validated['first_due_date'],
            'agent_id' => auth()->id(),
        ]);

        $this->created = true;
        $this->createdContractNumber = $loan->contract_number;
        $this->reset(['purpose', 'groupId']);
    }

    public function render(LoanService $loanService)
    {
        $type = LoanType::find($this->loanTypeId);
        $preview = $loanService->previewSchedule([
            'amount' => $this->amount,
            'interest_rate' => $this->interestRate,
            'duration_months' => $this->durationMonths,
            'repayment_frequency' => $this->repaymentFrequency,
            'first_due_date' => $this->firstDueDate,
            'grace_period_days' => $this->gracePeriodDays,
        ]);

        return view('livewire.loan-create', [
            'clients' => $this->clientsQuery()->get(),
            'groups' => $this->groupsQuery()->withCount([
                'members as members_count' => fn (Builder $query) => $query
                    ->where('users.role', 'client')
                    ->where('users.is_active', true),
            ])->get(),
            'loanTypes' => LoanType::where('is_active', true)->orderBy('name')->get(),
            'selectedType' => $type,
            'preview' => $preview,
            'totalPreview' => $preview->sum('amount_due'),
        ]);
    }

    protected function clientsQuery(): Builder
    {
        $user = auth()->user();

        return User::query()
            ->where('role', 'client')
            ->where('is_active', true)
            ->when($user->role === 'agent' && $user->zone_id, fn (Builder $query) => $query->where('zone_id', $user->zone_id))
            ->orderBy('name');
    }

    protected function groupsQuery(): Builder
    {
        $user = auth()->user();

        return Group::query()
            ->where('status', 'active')
            ->when($user->role === 'agent' && $user->zone_id, fn (Builder $query) => $query->where('zone_id', $user->zone_id))
            ->orderBy('name');
    }
}
