<?php

namespace App\Livewire;

use App\Models\Loan;
use App\Services\LoanService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layout')]
class LoanRequestReview extends Component
{
    public int $loanId;

    public function mount(int $loanId): void
    {
        $this->loanId = $loanId;
    }

    public function approve(LoanService $loanService): void
    {
        $reviewer = auth()->user();
        abort_unless(in_array($reviewer->role, ['admin', 'agent'], true), 403);

        $loanService->reviewRequest(
            Loan::findOrFail($this->loanId),
            $reviewer,
            true
        );

        $this->dispatch('loan-request-reviewed');
        $this->redirect($reviewer->role === 'admin' ? url('/admin/dashboard') : url('/agent/dashboard'), navigate: true);
    }

    public function reject(LoanService $loanService): void
    {
        $reviewer = auth()->user();
        abort_unless(in_array($reviewer->role, ['admin', 'agent'], true), 403);

        $loanService->reviewRequest(
            Loan::findOrFail($this->loanId),
            $reviewer,
            false
        );

        $this->dispatch('loan-request-reviewed');
        $this->redirect($reviewer->role === 'admin' ? url('/admin/dashboard') : url('/agent/dashboard'), navigate: true);
    }
}
