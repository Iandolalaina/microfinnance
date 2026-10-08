<?php

namespace App\Livewire;

use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Zone;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layout')]
class AdminDashboard extends Component
{
    use WithPagination;

    public function render()
    {
        // Chiffres globaux sur toute l'ONG
        $totalClients = User::where('role', 'client')->count();
        $totalAgents = User::where('role', 'agent')->count();
        $totalLoansAmount = Loan::where('status', 'active')->sum('amount');
        $totalRepaid = Schedule::sum('amount_paid') + LoanSchedule::sum('amount_paid');
        $totalDue = Schedule::sum('amount_due') + LoanSchedule::sum('amount_due');
        $totalRemaining = $totalDue - $totalRepaid;

        // Répartition des échéances (même logique que le dashboard agent,
        // mais sans filtrer par zone cette fois)
        $schedules = Schedule::all()->concat(LoanSchedule::all());
        $stats = [
            'paid' => $schedules->where('effective_status', 'paid')->count(),
            'pending' => $schedules->where('effective_status', 'pending')->count(),
            'late' => $schedules->where('effective_status', 'late')->count(),
        ];

        // Nombre de clients par zone, pour repérer où se concentre l'activité
        $zones = Zone::withCount(['users as clients_count' => function ($query) {
            $query->where('role', 'client');
        }])->get();
        $pendingLoans = Loan::with(['client', 'type', 'group'])
            ->where('status', 'pending')
            ->latest()
            ->get();
        $activeLoans = Loan::with(['client', 'type', 'loanSchedules', 'schedules'])
            ->where('status', 'active')
            ->latest()
            ->paginate(10);

        return view('livewire.admin-dashboard', [
            'totalClients' => $totalClients,
            'totalAgents' => $totalAgents,
            'totalLoansAmount' => $totalLoansAmount,
            'totalRepaid' => $totalRepaid,
            'totalRemaining' => $totalRemaining,
            'stats' => $stats,
            'zones' => $zones,
            'pendingLoans' => $pendingLoans,
            'activeLoans' => $activeLoans,
        ]);
    }
}
