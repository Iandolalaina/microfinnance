<div class="max-w-md mx-auto px-4 py-6 pb-24">

    {{-- Bandeau de marque --}}
    <p class="text-center text-brand-600 font-bold tracking-wide text-sm mb-4">MITSINJO</p>

    {{-- En-tête avec nom du client et déconnexion --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-gray-500">Bonjour,</p>
            <h1 class="text-lg font-semibold text-gray-900">{{ auth()->user()->name }}</h1>
        </div>
        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-400 hover:text-red-600">
                Déconnexion
            </button>
        </form>
    </div>

    @if (! $loan)
        {{-- Cas où le client n'a aucun prêt actif en ce moment --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 text-center">
            <p class="text-gray-500 text-sm">
                Vous n'avez actuellement aucun crédit actif.
            </p>
        </div>
    @else
        {{-- Carte principale : résumé du crédit --}}
        <div class="bg-brand-600 rounded-2xl p-5 text-white shadow-sm mb-4">
            <p class="text-brand-100 text-xs uppercase tracking-wide mb-1">Crédit en cours</p>
            <p class="text-3xl font-bold mb-4">
                {{ number_format($loan->amount, 0, ',', ' ') }} Ar
            </p>

            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-brand-500">
                <div>
                    <p class="text-brand-100 text-xs mb-0.5">Reste à payer</p>
                    <p class="font-semibold">{{ number_format($loan->remaining_amount, 0, ',', ' ') }} Ar</p>
                </div>
                <div>
                    <p class="text-brand-100 text-xs mb-0.5">Déjà remboursé</p>
                    <p class="font-semibold">{{ number_format($loan->total_paid, 0, ',', ' ') }} Ar</p>
                </div>
            </div>
        </div>

        {{-- Carte : prochaine échéance --}}
        @php $next = $loan->nextSchedule(); @endphp
        @if ($next)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6">
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Prochaine échéance</p>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ number_format($next->amount_due - $next->amount_paid, 0, ',', ' ') }} Ar
                        </p>
                        <p class="text-sm text-gray-500">
                            à régler avant le {{ \Carbon\Carbon::parse($next->due_date)->translatedFormat('d F Y') }}
                        </p>
                    </div>
                    <a href="{{ url('/client/payment/' . $next->id) }}"
                       class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl">
                        Payer
                    </a>
                </div>
            </div>
        @endif

        {{-- Liste de toutes les échéances --}}
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Échéancier complet</h2>
        <div class="space-y-2">
            @foreach ($schedules as $schedule)
                <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($schedule->due_date)->translatedFormat('d M Y') }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ number_format($schedule->amount_due, 0, ',', ' ') }} Ar
                        </p>
                    </div>

                    {{-- Badge de statut coloré selon l'état de l'échéance --}}
                    @php
                        $badgeClasses = match($schedule->status) {
                            'paid' => 'bg-green-100 text-green-700',
                            'late' => 'bg-red-100 text-red-700',
                            'partial' => 'bg-amber-100 text-amber-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                        $badgeLabel = match($schedule->status) {
                            'paid' => 'Payé',
                            'late' => 'En retard',
                            'partial' => 'Partiel',
                            default => 'En attente',
                        };
                    @endphp
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $badgeClasses }}">
                        {{ $badgeLabel }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif

</div>
