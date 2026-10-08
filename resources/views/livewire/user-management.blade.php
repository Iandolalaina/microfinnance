<div class="max-w-3xl mx-auto px-4 py-6 pb-16">

    <div class="sticky-page-header flex items-center justify-between">
        <div>
            <p class="text-brand-600 font-bold tracking-wide text-sm">MITSINJO — {{ auth()->user()->isAgent() ? 'Espace Agent' : 'Administration' }}</p>
            <h1 class="text-lg font-semibold text-gray-900 mt-1">Gestion des comptes</h1>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-brand-600">Accueil</a>
            <a href="{{ auth()->user()->isAgent() ? url('/agent/dashboard') : url('/admin/dashboard') }}" class="text-sm text-gray-400">← Retour</a>
        </div>
    </div>

    <button
        wire:click="toggleForm"
        class="mb-4 text-sm bg-brand-600 hover:bg-brand-700 text-white font-medium px-4 py-2.5 rounded-xl"
    >
        {{ $showForm ? 'Annuler' : '+ Nouvel utilisateur' }}
    </button>

    @if ($showForm)
        <div
            wire:click.self="closeForm"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/60 p-4"
            role="presentation"
        >
        <section
            role="dialog"
            aria-modal="true"
            aria-labelledby="user-form-title"
            class="my-auto w-full max-w-xl rounded-2xl border border-gray-100 bg-white p-6 shadow-2xl"
        >
            @php
                $editingUser = $editingUserId ? $users->firstWhere('id', $editingUserId) : null;
            @endphp
            <div class="mb-4 flex items-center justify-between gap-4">
            <h2 id="user-form-title" class="text-sm font-semibold text-gray-900">
                {{ $editingUserId ? 'Modifier un utilisateur' : 'Créer un utilisateur' }}
            </h2>
            <button
                type="button"
                wire:click="closeForm"
                aria-label="Fermer"
                class="rounded-lg px-2 py-1 text-2xl leading-none text-gray-500 hover:bg-gray-100 hover:text-gray-900"
            >&times;</button>
            </div>
            <form wire:submit="{{ $editingUserId ? 'update' : 'create' }}" class="space-y-4">

                <div class="flex flex-col items-center rounded-xl bg-gray-50 p-5">
                    <label class="mb-3 block text-sm font-medium text-gray-700">Photo de profil (facultative)</label>
                    @if ($profilePhoto)
                        <img src="{{ $profilePhoto->temporaryUrl() }}" alt="Aperçu de la photo de profil" class="mb-4 h-40 w-40 rounded-xl object-cover ring-4 ring-white shadow">
                    @elseif ($editingUser?->profile_photo)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($editingUser->profile_photo) }}" alt="Photo de profil actuelle" class="mb-4 h-40 w-40 rounded-xl object-cover ring-4 ring-white shadow">
                    @endif
                    <input type="file" wire:model="profilePhoto" accept="image/jpeg,image/png,image/webp"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3">
                    @error('profilePhoto') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

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
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Mot de passe {{ $editingUserId ? '(laisser vide pour ne pas le modifier)' : '' }}
                    </label>
                    <input type="password" wire:model="password" autocomplete="new-password"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                @if (auth()->user()->isAgent())
                    <div class="rounded-xl bg-gray-50 p-3 text-sm text-gray-600">
                        Compte client · Zone : {{ auth()->user()->zone?->name ?? 'Aucune' }}
                    </div>
                @else
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
                        @error('zoneId') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif

                <button type="submit" wire:loading.attr="disabled"
                    class="w-full bg-brand-600 hover:bg-brand-700 text-white font-medium py-3 rounded-xl disabled:opacity-60">
                    {{ $editingUserId ? 'Enregistrer les modifications' : 'Créer le compte' }}
                </button>
            </form>
        </section>
        </div>
    @endif

    <div class="space-y-2">
        @foreach ($users as $user)
            <div wire:key="managed-user-{{ $user->id }}" class="bg-white rounded-xl border border-gray-100 p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            wire:click="viewUser({{ $user->id }})"
                            aria-haspopup="dialog"
                            class="flex items-center gap-3 rounded-xl text-left focus:outline-none focus:ring-2 focus:ring-brand-500"
                        >
                            <x-profile-avatar :user="$user" class="h-16 w-16" />
                            <span>
                                <span class="block text-sm font-medium text-gray-900">{{ $user->name }}</span>
                                <span class="block text-xs text-gray-400">{{ $user->phone }}</span>
                            </span>
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
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

                        <span class="text-xs font-medium px-2.5 py-1 rounded-full
                               {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $user->is_active ? 'Actif' : 'Désactivé' }}
                        </span>
                        <button wire:click="edit({{ $user->id }})"
                            class="text-xs font-medium text-brand-600 hover:underline">
                            Modifier
                        </button>
                        @if ($user->id !== auth()->id())
                            <button
                                wire:click="toggleActive({{ $user->id }})"
                                wire:confirm="{{ $user->is_active ? 'Désactiver ce compte ? Son historique sera conservé.' : 'Réactiver ce compte ?' }}"
                                class="text-xs font-medium {{ $user->is_active ? 'text-red-600' : 'text-green-700' }}"
                            >
                                {{ $user->is_active ? 'Désactiver' : 'Réactiver' }}
                            </button>
                            <button
                                wire:click="delete({{ $user->id }})"
                                wire:confirm="Supprimer définitivement le compte de {{ $user->name }} ? Ses prêts, paiements et autres données liées peuvent aussi être supprimés."
                                class="text-xs font-medium text-red-600 hover:underline"
                            >
                                Supprimer
                            </button>
                        @endif
                    </div>
                </div>

            </div>
        @endforeach
    </div>

    @error('delete') <p class="mt-3 text-sm text-red-600">{{ $message }}</p> @enderror

    @if ($selectedUserId)
        @php
            $selectedUser = $users->firstWhere('id', $selectedUserId);
        @endphp
        @if ($selectedUser)
            <div
                wire:click.self="viewUser({{ $selectedUser->id }})"
                class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4"
                role="presentation"
            >
                <section
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="selected-user-title"
                    class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl sm:p-8"
                >
                    <div class="mb-6 flex justify-end">
                        <button
                            type="button"
                            wire:click="viewUser({{ $selectedUser->id }})"
                            aria-label="Fermer"
                            class="rounded-lg px-2 py-1 text-2xl leading-none text-gray-500 hover:bg-gray-100 hover:text-gray-900"
                        >&times;</button>
                    </div>

                    <div class="mb-6 flex flex-col items-center text-center">
                        <x-profile-avatar :user="$selectedUser" class="h-48 w-48 text-6xl ring-4 ring-brand-100" />
                        <h2 id="selected-user-title" class="mt-5 text-xl font-semibold text-gray-900">{{ $selectedUser->name }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ ucfirst($selectedUser->role) }}</p>
                    </div>

                    <dl class="grid grid-cols-1 gap-4 border-t border-gray-100 pt-5 text-sm sm:grid-cols-2">
                        <div><dt class="text-xs text-gray-500">Téléphone</dt><dd class="mt-1 text-gray-900">{{ $selectedUser->phone }}</dd></div>
                        <div><dt class="text-xs text-gray-500">E-mail</dt><dd class="mt-1 text-gray-900">{{ $selectedUser->email ?: 'Non renseigné' }}</dd></div>
                        <div><dt class="text-xs text-gray-500">Matricule</dt><dd class="mt-1 text-gray-900">{{ $selectedUser->matricule ?: 'Non renseigné' }}</dd></div>
                        <div><dt class="text-xs text-gray-500">Zone</dt><dd class="mt-1 text-gray-900">{{ $selectedUser->zone?->name ?? 'Aucune' }}</dd></div>
                        <div><dt class="text-xs text-gray-500">Inscrit le</dt><dd class="mt-1 text-gray-900">{{ $selectedUser->created_at?->format('d/m/Y') ?? '—' }}</dd></div>
                    </dl>
                </section>
            </div>
        @endif
    @endif

</div>
