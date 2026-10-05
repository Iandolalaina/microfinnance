<?php

namespace App\Livewire;

use App\Models\Announcement;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layout')]
class ClientDashboard extends Component
{
    /**
     * render() est appelée automatiquement par Livewire pour construire
     * l'affichage. Tout ce qu'on passe à view() devient disponible
     * dans le fichier .blade.php associé.
     */
    public function render()
    {
        // On récupère le prêt ACTIF du client actuellement connecté.
        // auth()->user() retourne l'utilisateur connecté grâce à la session
        // ouverte lors du login (voir LoginController).
        $loan = auth()->user()
            ->loans()
            ->where('status', 'active')
            ->latest()
            ->first();
        $pendingLoan = auth()->user()->loans()
            ->where('status', 'pending')
            ->with('type')
            ->latest()
            ->first();
        $rejectedLoan = auth()->user()->loans()
            ->where('status', 'rejected')
            ->with('type')
            ->latest()
            ->first();

        // On récupère aussi TOUTES les échéances de ce prêt, triées par date,
        // uniquement si un prêt actif existe (sinon collection vide).
        $schedules = $loan
            ? ($loan->loanSchedules()->exists()
                ? $loan->loanSchedules()->orderBy('due_date')->get()
                : $loan->schedules()->orderBy('due_date')->get())
            : collect();

        $announcements = Announcement::visibleTo(auth()->user())
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->limit(10)
            ->get();

        return view('livewire.client-dashboard', [
            'loan' => $loan,
            'pendingLoan' => $pendingLoan,
            'rejectedLoan' => $rejectedLoan,
            'schedules' => $schedules,
            'announcements' => $announcements,
        ]);
    }
}
