<div class="max-w-4xl mx-auto px-4 py-6 pb-16">

    {{-- En-tête --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-brand-600 font-bold tracking-wide text-sm">MITSINJO — Espace Agent</p>
            <h1 class="text-lg font-semibold text-gray-900 mt-1">
                Bonjour, {{ auth()->user()->name }}
                @if($zone)
                    <span class="text-sm font-normal text-gray-500">· Zone : {{ $zone->name }}</span>
                @endif
            </h1>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ url('/agent/announcements') }}" class="text-sm text-brand-600 hover:underline">
                Publier une annonce
            </a>
            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-400 hover:text-red-600">Déconnexion</button>
            </form>
        </div>
    </div>

    {{-- Cartes statistiques --}}
    <div class="grid grid-cols-3 gap-3 mb-8">
        <div class="bg-green-50 border border-green-100 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-green-700">{{ $stats['paid'] }}</p>
            <p class="text-xs text-green-700 mt-1">Payées</p>
        </div>
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-gray-700">{{ $stats['pending'] }}</p>
            <p class="text-xs text-gray-600 mt-1">En attente</p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-red-700">{{ $stats['late'] }}</p>
            <p class="text-xs text-red-700 mt-1">En retard</p>
        </div>
    </div>

    {{-- Liste des clients --}}
    <h2 class="text-sm font-semibold text-gray-700 mb-3">
        Portefeuille clients ({{ $clients->count() }})
    </h2>

    <div class="space-y-3">
        @forelse ($clients as $client)
            @php $loan = $client->loans->first(); @endphp
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <div class="flex items-center justify-between mb-1">
                    <p class="font-medium text-gray-900">{{ $client->name }}</p>
                    <p class="text-xs text-gray-400">{{ $client->phone }}</p>
                </div>

                @if (! $loan)
                    <p class="text-sm text-gray-400 mt-2">Aucun crédit actif</p>
                @else
                    @php $next = $loan->nextSchedule(); @endphp
                    <div class="flex items-center justify-between mt-2">
                        <div class="text-sm text-gray-500">
                            Reste à payer :
                            <span class="font-semibold text-gray-800">
                                {{ number_format($loan->remaining_amount, 0, ',', ' ') }} Ar
                            </span>
                        </div>

                        @if ($next)
                            @php
                                $badgeClasses = match($next->effective_status) {
                                    'late' => 'bg-red-100 text-red-700',
                                    'partial' => 'bg-amber-100 text-amber-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                                $badgeLabel = match($next->effective_status) {
                                    'late' => 'En retard',
                                    'partial' => 'Partiel',
                                    default => 'En attente',
                                };
                            @endphp
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $badgeClasses }}">
                                    {{ $badgeLabel }}
                                </span>
                                <a href="{{ url('/agent/manual-payment/' . $next->id) }}"
                                   class="text-xs bg-brand-600 hover:bg-brand-700 text-white font-medium px-3 py-1.5 rounded-lg">
                                    Enregistrer un versement
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-400 text-center py-6">Aucun client dans votre zone pour l'instant.</p>
        @endforelse
    </div>

</div>
