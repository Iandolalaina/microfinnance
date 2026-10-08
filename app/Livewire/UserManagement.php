<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Zone;
use Illuminate\Validation\Rule;
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

    public ?int $editingUserId = null;

    public function toggleForm(): void
    {
        $this->authorizeAdmin();
        $this->resetForm();
        $this->showForm = ! $this->showForm;
    }

    public function create(): void
    {
        $this->authorizeAdmin();
        $validated = $this->validate($this->rules(), $this->messages());

        User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'password' => $this->password,
            'role' => $validated['role'],
            'zone_id' => $validated['zoneId'],
        ]);

        $this->resetForm();
    }

    public function edit(int $userId): void
    {
        $this->authorizeAdmin();
        $user = User::findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->phone = $user->phone;
        $this->password = '';
        $this->role = $user->role;
        $this->zoneId = $user->zone_id;
        $this->showForm = true;
        $this->resetValidation();
    }

    public function update(): void
    {
        $this->authorizeAdmin();
        $user = User::findOrFail($this->editingUserId);
        $validated = $this->validate($this->rules($user), $this->messages());

        if ($user->id === auth()->id() && $validated['role'] !== 'admin') {
            $this->addError('role', 'Vous ne pouvez pas modifier votre propre rôle administrateur.');

            return;
        }

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'zone_id' => $validated['zoneId'],
            ...($this->password !== '' ? ['password' => $this->password] : []),
        ]);

        $this->resetForm();
    }

    public function toggleActive(User $user): void
    {
        $this->authorizeAdmin();

        if ($user->id === auth()->id()) {
            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
    }

    public function render()
    {
        $this->authorizeAdmin();

        return view('livewire.user-management', [
            'users' => User::orderBy('role')->orderBy('name')->get(),
            'zones' => Zone::orderBy('name')->get(),
        ]);
    }

    private function rules(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,agent,client'],
            'zoneId' => ['nullable', 'integer', 'exists:zones,id'],
        ];
    }

    private function messages(): array
    {
        return [
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
        ];
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'phone', 'password', 'role', 'zoneId', 'editingUserId']);
        $this->showForm = false;
        $this->resetValidation();
    }
}
