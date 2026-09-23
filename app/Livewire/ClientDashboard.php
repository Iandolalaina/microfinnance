<?php

namespace App\Livewire;

use App\Models\Loan;
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

        // On récupère aussi TOUTES les échéances de ce prêt, triées par date,
        // uniquement si un prêt actif existe (sinon collection vide).
        $schedules = $loan
            ? $loan->schedules()->orderBy('due_date')->get()
            : collect();

        return view('livewire.client-dashboard', [
            'loan' => $loan,
            'schedules' => $schedules,
        ]);
    }
}
