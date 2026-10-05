<div class="max-w-5xl mx-auto px-4 py-6 pb-16">

    @php
        $user = auth()->user();
        $isAdmin = $user->role === 'admin';
        $dashboardUrl = $isAdmin ? url('/admin/dashboard') : url('/agent/dashboard');
    @endphp

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <p class="text-brand-600 font-bold tracking-wide text-sm">MITSINJO - Annonces</p>
            <h1 class="text-lg font-semibold text-gray-900 mt-1">
                Gestion des annonces
            </h1>
            @if (! $isAdmin && $user->zone)
                <p class="text-sm text-gray-500 mt-1">Zone agent : {{ $user->zone->name }}</p>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-brand-600">Accueil</a>
            <a href="{{ $dashboardUrl }}" class="text-sm text-gray-400">Retour</a>
        </div>
    </div>

    @if ($published)
        <div class="bg-green-50 border border-green-100 rounded-xl p-4 mb-5 text-sm text-green-700" role="status">
            {{ $deliveryMessage }}
        </div>
    @endif

    @if ($deletedAnnouncementId)
        <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 mb-5 text-sm text-amber-700" role="status">
            Annonce supprimee.
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[minmax(0,380px)_1fr]">
        <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 h-fit">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Publier une annonce</h2>

            <form wire:submit="publish" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Titre</label>
                    <input
                        type="text"
                        wire:model="title"
                        placeholder="Ex : Reunion d'information samedi"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-brand-500"
                    >
                    @error('title') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Message</label>
                    <textarea
                        wire:model="content"
                        rows="5"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-brand-500"
                    ></textarea>
                    @error('content') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Zone ciblee</label>

                    @if ($isAdmin)
                        <select
                            wire:model="zoneId"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-brand-500"
                        >
                            <option value="">Toutes les zones</option>
                            @foreach ($zones as $zone)
                                <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                            {{ $user->zone?->name ?? 'Aucune zone rattachee' }}
                        </div>
                    @endif
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full bg-brand-600 hover:bg-brand-700 text-white font-medium py-3.5 rounded-xl disabled:opacity-60"
                >
                    Publier l'annonce
                </button>
            </form>
        </section>

        <section>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-700">
                    Annonces deja envoyees ({{ $announcements->count() }})
                </h2>
            </div>

            <div class="space-y-3">
                @forelse ($announcements as $announcement)
                    <article class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <h3 class="font-medium text-gray-900">{{ $announcement->title }}</h3>
                                    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $announcement->zone ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $announcement->zone?->name ?? 'Toutes les zones' }}
                                    </span>
                                </div>

                                <p class="text-xs text-gray-400">
                                    Envoyee le {{ $announcement->published_at?->format('d/m/Y a H:i') ?? $announcement->created_at->format('d/m/Y a H:i') }}
                                    par {{ $announcement->author?->name ?? 'Utilisateur supprime' }}
                                </p>

                                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-600">
                                    {{ $announcement->content }}
                                </p>
                            </div>

                            <button
                                type="button"
                                wire:click="delete({{ $announcement->id }})"
                                wire:confirm="Supprimer cette annonce ?"
                                class="shrink-0 text-xs font-medium px-3 py-2 rounded-lg bg-red-50 text-red-700 hover:bg-red-100"
                            >
                                Supprimer
                            </button>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-gray-100 pt-3">
                            <span class="text-xs text-gray-500">
                                {{ $announcement->target_clients_count }} client(s) concerne(s)
                            </span>
                            @if ($announcement->target_clients_preview !== [])
                                <span class="text-xs text-gray-400">
                                    {{ implode(', ', $announcement->target_clients_preview) }}
                                    @if ($announcement->target_clients_count > count($announcement->target_clients_preview))
                                        +{{ $announcement->target_clients_count - count($announcement->target_clients_preview) }}
                                    @endif
                                </span>
                            @endif
                            @if ($announcement->zone)
                                <span class="text-xs text-gray-400">Liee aux utilisateurs de cette zone</span>
                            @else
                                <span class="text-xs text-gray-400">Visible par tous les comptes client actifs</span>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="bg-white rounded-xl border border-gray-100 p-6 text-center">
                        <p class="text-sm text-gray-400">Aucune annonce envoyee pour le moment.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

</div>
