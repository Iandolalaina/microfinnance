<div class="max-w-4xl mx-auto px-4 py-6 pb-16">

    {{-- En-tête --}}
    <div class="sticky-page-header flex items-center justify-between">
        <div>
            <p class="text-brand-600 font-bold tracking-wide text-sm">MITSINJO — Administration</p>
            <div class="mt-1 flex items-center gap-3">
                <x-profile-avatar :user="auth()->user()" class="h-14 w-14 text-sm" />
                <h1 class="text-lg font-semibold text-gray-900">Bonjour, {{ auth()->user()->name }}</h1>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-brand-600">Accueil</a>
            <a href="{{ url('/admin/loans/create') }}" class="text-sm text-brand-600 hover:underline">Octroyer un pret</a>
            <a href="{{ url('/admin/announcements') }}" class="text-sm text-brand-600 hover:underline">Envoyer une information</a>
            <a href="{{ url('/admin/users') }}" class="text-sm text-brand-600 hover:underline">
                Gérer les utilisateurs
            </a>
            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-400 hover:text-red-600">Déconnexion</button>
            </form>
        </div>
    </div>

    <section class="mb-8" aria-labelledby="pending-loans-heading">
        <h2 id="pending-loans-heading" class="mb-3 text-sm font-semibold text-gray-700">
            Demandes de crédit à examiner ({{ $pendingLoans->count() }})
        </h2>
        <div class="space-y-3">
            @forelse ($pendingLoans as $loan)
                <article wire:key="admin-loan-request-{{ $loan->id }}" class="rounded-xl border border-amber-100 bg-white p-4 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $loan->client->name }} — {{ $loan->type?->name }}</p>
                            <p class="mt-1 text-sm text-gray-600">{{ number_format($loan->amount, 0, ',', ' ') }} Ar · {{ $loan->duration_months }} mois · {{ $loan->repayment_frequency }}</p>
                            <p class="mt-1 text-sm text-gray-500">{{ $loan->purpose }}</p>
                            @if ($loan->group)
                                <p class="mt-1 text-xs text-gray-500">Groupe : {{ $loan->group->name }}</p>
                            @endif
                        </div>
                        <livewire:loan-request-review :loan-id="$loan->id" :key="'admin-review-'.$loan->id" />
                    </div>
                </article>
            @empty
                <p class="rounded-xl bg-white p-4 text-sm text-gray-500">Aucune demande en attente.</p>
            @endforelse
        </div>
    </section>

    <section class="mb-8" aria-labelledby="active-loans-heading">
        <h2 id="active-loans-heading" class="mb-3 text-sm font-semibold text-gray-700">
            Dossiers de crédit en cours ({{ $activeLoans->total() }})
        </h2>
        <div class="space-y-3">
            @forelse ($activeLoans as $loan)
                @php
                    $loanSchedules = $loan->loanSchedules->isNotEmpty() ? $loan->loanSchedules : $loan->schedules;
                    $repayableAmount = (float) $loan->total_repayable ?: (float) $loanSchedules->sum('amount_due');
                    $interestAmount = max(0, $repayableAmount - (float) $loan->amount);
                @endphp
                <article wire:key="admin-active-loan-{{ $loan->id }}" class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $loan->client->name }} — {{ $loan->type?->name ?? 'Crédit' }}</p>
                            <p class="mt-1 text-sm text-gray-600">
                                Prêt : {{ number_format($loan->amount, 0, ',', ' ') }} Ar ·
                                Intérêts : {{ number_format($interestAmount, 0, ',', ' ') }} Ar
                                ({{ number_format($loan->interest_rate, 2, ',', ' ') }} %)
                            </p>
                            <p class="mt-1 text-sm text-gray-600">Reste à payer : {{ number_format($loan->remaining_amount, 0, ',', ' ') }} Ar</p>
                        </div>
                    </div>

                    <div class="mt-4 border-t border-gray-100 pt-3">
                        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Dates d’échéance</h3>
                        <div class="space-y-2">
                            @forelse ($loanSchedules->sortBy('due_date') as $schedule)
                                <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                    <span class="text-gray-700">{{ $schedule->due_date->translatedFormat('d F Y') }}</span>
                                    <span class="text-gray-600">
                                        {{ number_format($schedule->amount_due, 0, ',', ' ') }} Ar
                                        @if ($schedule instanceof \App\Models\LoanSchedule)
                                            · intérêts : {{ number_format($schedule->interest_due, 0, ',', ' ') }} Ar
                                        @endif
                                    </span>
                                    <span class="font-medium text-gray-500">
                                        {{ match ($schedule->effective_status) {
                                            'paid' => 'Payée',
                                            'late' => 'En retard',
                                            'partial' => 'Partielle',
                                            default => 'En attente',
                                        } }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-xs text-gray-500">Aucune échéance enregistrée.</p>
                            @endforelse
                        </div>
                    </div>
                </article>
            @empty
                <p class="rounded-xl bg-white p-4 text-sm text-gray-500">Aucun crédit en cours.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $activeLoans->links() }}</div>
    </section>

    {{-- KPIs financiers globaux --}}
    <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="bg-brand-600 rounded-2xl p-5 text-white">
            <p class="text-brand-100 text-xs uppercase tracking-wide mb-1">Encours total prêté</p>
            <p class="text-2xl font-bold">{{ number_format($totalLoansAmount, 0, ',', ' ') }} Ar</p>
        </div>
        <div class="bg-gray-900 rounded-2xl p-5 text-white">
            <p class="text-gray-300 text-xs uppercase tracking-wide mb-1">Reste à recouvrer</p>
            <p class="text-2xl font-bold">{{ number_format($totalRemaining, 0, ',', ' ') }} Ar</p>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-3 mb-8">
        <div class="bg-white border border-gray-100 rounded-xl p-4 text-center">
            <p class="text-xl font-bold text-gray-900">{{ $totalClients }}</p>
            <p class="text-xs text-gray-500 mt-1">Clients</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-xl p-4 text-center">
            <p class="text-xl font-bold text-gray-900">{{ $totalAgents }}</p>
            <p class="text-xs text-gray-500 mt-1">Agents</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-xl p-4 text-center">
            <p class="text-xl font-bold text-gray-900">{{ number_format($totalRepaid, 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-500 mt-1">Ar remboursés</p>
        </div>
    </div>

    {{-- Répartition des échéances --}}
    <h2 class="text-sm font-semibold text-gray-700 mb-3">Échéances (toutes zones confondues)</h2>
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

    {{-- Répartition par zone --}}
    <h2 class="text-sm font-semibold text-gray-700 mb-3">Clients par zone</h2>
    <div class="space-y-2">
        @forelse ($zones as $zone)
            <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-center justify-between">
                <p class="text-sm font-medium text-gray-900">{{ $zone->name }}</p>
                <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-brand-50 text-brand-700">
                    {{ $zone->clients_count }} client(s)
                </span>
            </div>
        @empty
            <p class="text-sm text-gray-400 text-center py-6">Aucune zone enregistrée.</p>
        @endforelse
    </div>

</div>
