<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Zone;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('components.layout')]
class UserManagement extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $phone = '';

    public string $password = '';

    public string $role = 'client';

    public ?int $zoneId = null;

    public bool $showForm = false;

    public ?int $editingUserId = null;

    public ?int $selectedUserId = null;

    public ?TemporaryUploadedFile $profilePhoto = null;

    public function toggleForm(): void
    {
        $this->authorizeManager();

        if ($this->showForm) {
            $this->closeForm();

            return;
        }

        $this->resetForm();
        $this->selectedUserId = null;
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->authorizeManager();
        $this->resetForm();
    }

    public function create(): void
    {
        $this->authorizeManager();
        $validated = $this->validate($this->rules(), $this->messages());
        $manager = auth()->user();
        $photoPath = $this->profilePhoto?->store('profiles', 'public');

        User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'password' => $this->password,
            'role' => $manager->isAgent() ? 'client' : $validated['role'],
            'zone_id' => $manager->isAgent() ? $manager->zone_id : $validated['zoneId'],
            'profile_photo' => $photoPath,
        ]);

        $this->resetForm();
    }

    public function edit(int $userId): void
    {
        $user = $this->findManagedUserOrFail($userId);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->phone = $user->phone;
        $this->password = '';
        $this->role = $user->role;
        $this->zoneId = $user->zone_id;
        $this->profilePhoto = null;
        $this->selectedUserId = null;
        $this->showForm = true;
        $this->resetValidation();
    }

    public function update(): void
    {
        $user = $this->findManagedUserOrFail($this->editingUserId);
        $validated = $this->validate($this->rules($user), $this->messages());

        if ($user->id === auth()->id() && $validated['role'] !== 'admin') {
            $this->addError('role', 'Vous ne pouvez pas modifier votre propre rôle administrateur.');

            return;
        }

        $oldPhotoPath = $user->profile_photo;
        $photoPath = $this->profilePhoto?->store('profiles', 'public');

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'role' => auth()->user()->isAgent() ? 'client' : $validated['role'],
            'zone_id' => auth()->user()->isAgent() ? auth()->user()->zone_id : $validated['zoneId'],
            ...($photoPath ? ['profile_photo' => $photoPath] : []),
            ...($this->password !== '' ? ['password' => $this->password] : []),
        ]);

        if ($photoPath && $oldPhotoPath) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        $this->resetForm();
    }

    public function viewUser(int $userId): void
    {
        $this->findManagedUserOrFail($userId);
        $this->selectedUserId = $this->selectedUserId === $userId ? null : $userId;
        $this->showForm = false;
    }

    public function toggleActive(int $userId): void
    {
        $user = $this->findManagedUserOrFail($userId);

        if ($user->id === auth()->id()) {
            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
    }

    public function delete(int $userId): void
    {
        $user = $this->findManagedUserOrFail($userId);

        if ($user->id === auth()->id()) {
            $this->addError('delete', 'Vous ne pouvez pas supprimer votre propre compte.');

            return;
        }

        $photoPath = $user->profile_photo;
        $user->delete();

        if ($photoPath) {
            Storage::disk('public')->delete($photoPath);
        }

        $this->selectedUserId = null;
        $this->resetForm();
    }

    public function render()
    {
        $this->authorizeManager();
        $manager = auth()->user();
        $users = User::query()
            ->with('zone')
            ->when($manager->isAgent(), fn ($query) => $query
                ->where('role', 'client')
                ->where('zone_id', $manager->zone_id))
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return view('livewire.user-management', [
            'users' => $users,
            'zones' => $manager->isAdmin() ? Zone::orderBy('name')->get() : collect(),
        ]);
    }

    private function rules(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6'],
            'role' => auth()->user()->isAgent() ? ['nullable', 'in:client'] : ['required', 'in:admin,agent,client'],
            'zoneId' => auth()->user()->isAgent() ? ['nullable'] : ['nullable', 'integer', 'exists:zones,id'],
            'profilePhoto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    private function messages(): array
    {
        return [
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
            'profilePhoto.image' => 'Le fichier doit être une image.',
            'profilePhoto.mimes' => 'La photo doit être au format JPG, PNG ou WEBP.',
            'profilePhoto.max' => 'La photo ne doit pas dépasser 2 Mo.',
        ];
    }

    private function authorizeManager(): void
    {
        abort_unless(auth()->user()?->isAdmin() || auth()->user()?->isAgent(), 403);
    }

    private function findManagedUserOrFail(?int $userId): User
    {
        $this->authorizeManager();

        $manager = auth()->user();
        $query = User::query()->whereKey($userId);

        if ($manager->isAgent()) {
            $query->where('role', 'client')->where('zone_id', $manager->zone_id);
        }

        return $query->firstOrFail();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'phone', 'password', 'role', 'zoneId', 'editingUserId', 'profilePhoto']);
        $this->showForm = false;
        $this->resetValidation();
    }
}
