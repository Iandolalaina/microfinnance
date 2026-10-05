<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\LoanType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanService
{
    public function submitRequest(User $client, array $data): Loan
    {
        return DB::transaction(function () use ($client, $data) {
            $type = LoanType::findOrFail($data['loan_type_id']);
            $requestData = [
                ...$data,
                'interest_rate' => $type->effective_default_interest_rate,
            ];

            $this->validateBusinessRules($type, $client, $requestData);

            if ($client->loans()->where('status', 'pending')->exists()) {
                throw ValidationException::withMessages([
                    'loan_type_id' => 'Vous avez deja une demande de credit en attente.',
                ]);
            }

            $amount = (float) $data['amount'];
            $durationMonths = (int) $data['duration_months'];

            return $client->loans()->create([
                'loan_type_id' => $type->id,
                'group_id' => $data['group_id'] ?? null,
                'solidarity_type' => empty($data['group_id']) ? 'individual' : 'group',
                'amount' => $amount,
                'interest_rate' => $type->effective_default_interest_rate,
                'total_repayable' => $this->totalRepayable(
                    $amount,
                    $type->effective_default_interest_rate,
                    $durationMonths
                ),
                'purpose' => $data['purpose'],
                'duration_months' => $durationMonths,
                'repayment_frequency' => $data['repayment_frequency'],
                'grace_period_days' => (int) ($data['grace_period_days'] ?? 0),
                'status' => 'pending',
            ]);
        });
    }

    public function reviewRequest(Loan $loan, User $reviewer, bool $approve): Loan
    {
        return DB::transaction(function () use ($loan, $reviewer, $approve) {
            $loan = Loan::whereKey($loan->id)->lockForUpdate()->firstOrFail();

            if ($loan->status !== 'pending') {
                throw ValidationException::withMessages([
                    'loan' => 'Cette demande a deja ete traitee.',
                ]);
            }

            if (! in_array($reviewer->role, ['admin', 'agent'], true)) {
                abort(403);
            }

            $loan->loadMissing('client', 'type');

            if ($reviewer->role === 'agent' && $reviewer->zone_id !== $loan->client->zone_id) {
                abort(403);
            }

            $loan->reviewed_by = $reviewer->id;
            $loan->reviewed_at = now();

            if (! $approve) {
                $loan->status = 'rejected';
                $loan->save();

                return $loan;
            }

            $loan->interest_rate = $loan->type->effective_default_interest_rate;
            $loan->total_repayable = $this->totalRepayable(
                (float) $loan->amount,
                (float) $loan->interest_rate,
                (int) $loan->duration_months
            );
            $loan->first_due_date = today()->addMonth()->toDateString();
            $loan->approved_at = now();
            $loan->disbursed_at = now();
            $loan->contract_number ??= $this->nextContractNumber();
            $loan->status = 'active';

            if ($reviewer->role === 'agent') {
                $loan->agent_id = $reviewer->id;
            }

            $this->validateBusinessRules($loan->type, $loan->client, [
                'amount' => $loan->amount,
                'interest_rate' => $loan->interest_rate,
                'duration_months' => $loan->duration_months,
                'repayment_frequency' => $loan->repayment_frequency,
                'grace_period_days' => $loan->grace_period_days,
                'group_id' => $loan->group_id,
            ]);

            $loan->save();
            $this->generateSchedule($loan);

            return $loan->load('loanSchedules');
        });
    }

    public function createLoan(array $data): Loan
    {
        return DB::transaction(function () use ($data) {
            $type = LoanType::findOrFail($data['loan_type_id']);
            $client = User::findOrFail($data['user_id']);

            $this->validateBusinessRules($type, $client, $data);

            $amount = (float) $data['amount'];
            $interestRate = (float) $data['interest_rate'];
            $durationMonths = (int) $data['duration_months'];

            $loan = Loan::create([
                'user_id' => $data['user_id'],
                'agent_id' => $data['agent_id'] ?? auth()->id(),
                'loan_type_id' => $type->id,
                'group_id' => $data['group_id'] ?? null,
                'solidarity_type' => empty($data['group_id']) ? 'individual' : 'group',
                'amount' => $amount,
                'interest_rate' => $interestRate,
                'total_repayable' => $this->totalRepayable($amount, $interestRate, $durationMonths),
                'purpose' => $data['purpose'] ?? null,
                'contract_number' => $data['contract_number'] ?? $this->nextContractNumber(),
                'duration_months' => (int) $data['duration_months'],
                'repayment_frequency' => $data['repayment_frequency'],
                'grace_period_days' => (int) ($data['grace_period_days'] ?? 0),
                'first_due_date' => $data['first_due_date'],
                'status' => 'active',
                'disbursed_at' => now(),
                'approved_at' => now(),
            ]);

            $this->generateSchedule($loan);

            return $loan->load(['type', 'group', 'loanSchedules']);
        });
    }

    public function generateSchedule(Loan $loan): Collection
    {
        $loan->loanSchedules()->delete();

        return $this->previewSchedule([
            'amount' => (float) $loan->amount,
            'interest_rate' => (float) $loan->interest_rate,
            'duration_months' => (int) $loan->duration_months,
            'repayment_frequency' => $loan->repayment_frequency,
            'first_due_date' => $loan->first_due_date?->toDateString(),
            'grace_period_days' => (int) $loan->grace_period_days,
        ])->map(function (array $line) use ($loan) {
            return $loan->loanSchedules()->create($line);
        });
    }

    public function previewSchedule(array $data): Collection
    {
        $amount = max(0, (float) ($data['amount'] ?? 0));
        $interestRate = max(0, (float) ($data['interest_rate'] ?? 0));
        $durationMonths = max(1, (int) ($data['duration_months'] ?? 1));
        $frequency = $data['repayment_frequency'] ?? 'monthly';
        $installments = $this->installmentCount($durationMonths, $frequency);
        $firstDueDate = CarbonImmutable::parse($data['first_due_date'] ?? now()->addMonth())
            ->addDays((int) ($data['grace_period_days'] ?? 0));

        $principalTotal = round($amount, 2);
        $interestTotal = round($amount * ($interestRate / 100) * $durationMonths, 2);
        $total = $principalTotal + $interestTotal;
        $principalPart = round($principalTotal / $installments, 2);
        $interestPart = round($interestTotal / $installments, 2);

        return collect(range(1, $installments))->map(function (int $number) use (
            $frequency,
            $firstDueDate,
            $installments,
            $principalTotal,
            $interestTotal,
            $total,
            $principalPart,
            $interestPart
        ) {
            $principalDue = $number === $installments
                ? round($principalTotal - ($principalPart * ($installments - 1)), 2)
                : $principalPart;

            $interestDue = $number === $installments
                ? round($interestTotal - ($interestPart * ($installments - 1)), 2)
                : $interestPart;

            return [
                'installment_number' => $number,
                'due_date' => $this->dueDate($firstDueDate, $frequency, $number),
                'principal_due' => $principalDue,
                'interest_due' => $interestDue,
                'amount_due' => $number === $installments
                    ? round($total - (($principalPart + $interestPart) * ($installments - 1)), 2)
                    : round($principalDue + $interestDue, 2),
                'amount_paid' => 0,
                'status' => 'pending',
            ];
        });
    }

    public function applyPayment(LoanSchedule $schedule, float $amount): LoanSchedule
    {
        if (! is_finite($amount) || $amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Le montant du versement doit etre superieur a zero.',
            ]);
        }

        return DB::transaction(function () use ($schedule, $amount) {
            $lockedSchedule = LoanSchedule::whereKey($schedule->id)
                ->lockForUpdate()
                ->firstOrFail();

            $remaining = max(0, (float) $lockedSchedule->amount_due - (float) $lockedSchedule->amount_paid);

            if ($amount > $remaining) {
                throw ValidationException::withMessages([
                    'amount' => 'Le versement depasse le montant restant de cette echeance.',
                ]);
            }

            $lockedSchedule->amount_paid = round((float) $lockedSchedule->amount_paid + $amount, 2);

            if ((float) $lockedSchedule->amount_paid >= (float) $lockedSchedule->amount_due) {
                $lockedSchedule->status = 'paid';
                $lockedSchedule->paid_at = now();
            } elseif ((float) $lockedSchedule->amount_paid > 0) {
                $lockedSchedule->status = $lockedSchedule->due_date->isBefore(today()) ? 'late' : 'partial';
            } else {
                $lockedSchedule->status = $lockedSchedule->due_date->isBefore(today()) ? 'late' : 'pending';
            }

            $lockedSchedule->save();

            return $lockedSchedule;
        });
    }

    protected function validateBusinessRules(LoanType $type, User $client, array $data): void
    {
        $frequency = $data['repayment_frequency'];
        $durationMonths = (int) $data['duration_months'];
        $gracePeriodDays = (int) ($data['grace_period_days'] ?? 0);

        if (! $type->is_active) {
            throw ValidationException::withMessages([
                'loan_type_id' => "Ce type de credit n'est plus disponible.",
            ]);
        }

        if ($client->role !== 'client' || ! $client->is_active) {
            throw ValidationException::withMessages([
                'user_id' => 'Le titulaire du pret doit etre un membre actif.',
            ]);
        }

        $interestRate = (float) ($data['interest_rate'] ?? -1);
        if (! is_finite($interestRate) || $interestRate < 0 || $interestRate > 100) {
            throw ValidationException::withMessages([
                'interest_rate' => 'Le taux doit etre compris entre 0 et 100 %.',
            ]);
        }

        if (isset($data['amount']) && $type->effective_max_amount !== null && (float) $data['amount'] > $type->effective_max_amount) {
            throw ValidationException::withMessages([
                'amount' => 'Le montant depasse le plafond de ce type de credit.',
            ]);
        }

        if (! in_array($frequency, $type->allowed_frequencies ?? [], true)) {
            throw ValidationException::withMessages([
                'repayment_frequency' => 'Cette frequence ne correspond pas au type de credit choisi.',
            ]);
        }

        if ($durationMonths < $type->min_duration_months || $durationMonths > $type->max_duration_months) {
            throw ValidationException::withMessages([
                'duration_months' => "La duree doit etre entre {$type->min_duration_months} et {$type->max_duration_months} mois.",
            ]);
        }

        if ($gracePeriodDays > 0 && ! $type->allows_grace_period) {
            throw ValidationException::withMessages([
                'grace_period_days' => 'Ce type de credit ne permet pas de periode de grace.',
            ]);
        }

        if (! empty($data['group_id'])) {
            $group = Group::findOrFail($data['group_id']);
            $eligibleMembers = $group->members()
                ->where('users.role', 'client')
                ->where('users.is_active', true);
            $membersCount = (clone $eligibleMembers)->count();

            if ($group->status !== 'active') {
                throw ValidationException::withMessages([
                    'group_id' => "Ce groupe solidaire n'est pas actif.",
                ]);
            }

            if ($membersCount < 3 || $membersCount > 5) {
                throw ValidationException::withMessages([
                    'group_id' => 'Un groupe solidaire doit contenir entre 3 et 5 membres.',
                ]);
            }

            if (! $eligibleMembers->whereKey($client->id)->exists()) {
                throw ValidationException::withMessages([
                    'user_id' => 'Le membre emprunteur doit appartenir au groupe solidaire selectionne.',
                ]);
            }

            $agent = auth()->user();
            if ($agent?->role === 'agent' && $agent->zone_id !== $group->zone_id) {
                throw ValidationException::withMessages([
                    'group_id' => 'Ce groupe ne se trouve pas dans votre zone.',
                ]);
            }
        }

        $agent = auth()->user();
        if ($agent?->role === 'agent' && $agent->zone_id !== $client->zone_id) {
            throw ValidationException::withMessages([
                'user_id' => 'Ce membre ne se trouve pas dans votre zone.',
            ]);
        }
    }

    protected function installmentCount(int $durationMonths, string $frequency): int
    {
        return match ($frequency) {
            'weekly' => max(1, (int) ceil($durationMonths * 4.345)),
            'biweekly' => max(1, (int) ceil($durationMonths * 2.1725)),
            default => max(1, $durationMonths),
        };
    }

    protected function dueDate(CarbonImmutable $firstDueDate, string $frequency, int $number): string
    {
        return match ($frequency) {
            'weekly' => $firstDueDate->addWeeks($number - 1)->toDateString(),
            'biweekly' => $firstDueDate->addWeeks(($number - 1) * 2)->toDateString(),
            default => $firstDueDate->addMonthsNoOverflow($number - 1)->toDateString(),
        };
    }

    protected function totalRepayable(float $amount, float $interestRate, int $durationMonths): float
    {
        $interest = $amount * ($interestRate / 100) * $durationMonths;

        return round($amount + $interest, 2);
    }

    protected function nextContractNumber(): string
    {
        return 'LOAN-'.now()->format('Ymd-His').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
    }
}
