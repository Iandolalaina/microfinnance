<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Zone;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layout')]
class UserManagement extends Component
{
    public string $name = '';
    public string $phone = '';
    public string $password = '';
    public string $role = 'client';
    public ?int $zoneId = null;

    public bool $showForm = false;

    /**
     * Affiche ou cache le formulaire de création (évite d'avoir
     * une 2e page séparée pour un simple formulaire).
     */
    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;
    }

    public function create(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,agent,client'],
        ], [
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
        ]);

        User::create([
            'name' => $this->name,
            'phone' => $this->phone,
            'password' => bcrypt($this->password),
            'role' => $this->role,
            'zone_id' => $this->zoneId,
        ]);

        $this->reset(['name', 'phone', 'password', 'role', 'zoneId']);
        $this->showForm = false;
    }

    /**
     * Active ou désactive un compte (au lieu de le supprimer, on garde
     * son historique intact — voir la colonne is_active de la migration).
     */
    public function toggleActive(User $user): void
    {
        // Sécurité : un admin ne peut pas se désactiver lui-même par erreur
        if ($user->id === auth()->id()) {
            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
    }

    public function render()
    {
        return view('livewire.user-management', [
            'users' => User::orderBy('role')->orderBy('name')->get(),
            'zones' => Zone::orderBy('name')->get(),
        ]);
    }
}
