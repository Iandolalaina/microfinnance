<div class="max-w-3xl mx-auto px-4 py-6 pb-16">

    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-brand-600 font-bold tracking-wide text-sm">MITSINJO — Utilisateurs</p>
            <h1 class="text-lg font-semibold text-gray-900 mt-1">Gestion des comptes</h1>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-brand-600">Accueil</a>
            <a href="{{ url('/admin/dashboard') }}" class="text-sm text-gray-400">← Retour</a>
        </div>
    </div>

    {{-- Bouton pour ouvrir/fermer le formulaire de création --}}
    <button
        wire:click="toggleForm"
        class="mb-4 text-sm bg-brand-600 hover:bg-brand-700 text-white font-medium px-4 py-2.5 rounded-xl"
    >
        {{ $showForm ? 'Annuler' : '+ Nouvel utilisateur' }}
    </button>

    @if ($showForm)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
            <form wire:submit="create" class="space-y-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nom complet</label>
                    <input type="text" wire:model="name"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Téléphone</label>
                    <input type="tel" wire:model="phone" placeholder="034 00 000 00"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('phone') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Mot de passe</label>
                    <input type="password" wire:model="password"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Rôle</label>
                    <select wire:model="role"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="client">Client</option>
                        <option value="agent">Agent</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Zone (facultatif)</label>
                    <select wire:model="zoneId"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Aucune</option>
                        @foreach ($zones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" wire:loading.attr="disabled"
                    class="w-full bg-brand-600 hover:bg-brand-700 text-white font-medium py-3 rounded-xl disabled:opacity-60">
                    Créer le compte
                </button>
            </form>
        </div>
    @endif

    {{-- Liste des utilisateurs --}}
    <div class="space-y-2">
        @foreach ($users as $user)
            <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                    <p class="text-xs text-gray-400">{{ $user->phone }}</p>
                </div>

                <div class="flex items-center gap-2">
                    @php
                        $roleBadge = match($user->role) {
                            'admin' => 'bg-purple-100 text-purple-700',
                            'agent' => 'bg-blue-100 text-blue-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $roleBadge }}">
                        {{ ucfirst($user->role) }}
                    </span>

                    @if ($user->id !== auth()->id())
                        <button
                            wire:click="toggleActive({{ $user->id }})"
                            class="text-xs font-medium px-2.5 py-1 rounded-full
                                   {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}"
                        >
                            {{ $user->is_active ? 'Actif' : 'Désactivé' }}
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

</div>
