<?php

namespace App\Livewire;

use App\Models\Loan;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layout')]
class AgentDashboard extends Component
{
    public function render()
    {
        $agent = auth()->user();

        // On récupère tous les CLIENTS de la MÊME ZONE que cet agent,
        // avec leur prêt actif et les échéances de ce prêt (en une seule
        // requête optimisée grâce à "with" — évite le problème dit "N+1").
        $clients = User::where('role', 'client')
            ->where('zone_id', $agent->zone_id)
            ->with(['loans' => function ($query) {
                $query->where('status', 'active')->with(['schedules', 'loanSchedules']);
            }])
            ->get();

        // On rassemble TOUTES les échéances de TOUS ces clients dans une
        // seule liste, pour pouvoir calculer les statistiques globales.
        $allSchedules = $clients->flatMap(fn ($client) => $client->loans->flatMap(
            fn ($loan) => $loan->loanSchedules->isNotEmpty() ? $loan->loanSchedules : $loan->schedules
        ));

        $stats = [
            'paid' => $allSchedules->where('effective_status', 'paid')->count(),
            'pending' => $allSchedules->where('effective_status', 'pending')->count(),
            'late' => $allSchedules->where('effective_status', 'late')->count(),
        ];
        $pendingLoans = Loan::with(['client', 'type', 'group'])
            ->where('status', 'pending')
            ->whereHas('client', fn ($query) => $query->where('zone_id', $agent->zone_id))
            ->latest()
            ->get();

        return view('livewire.agent-dashboard', [
            'clients' => $clients,
            'stats' => $stats,
            'zone' => $agent->zone,
            'pendingLoans' => $pendingLoans,
        ]);
    }
}
