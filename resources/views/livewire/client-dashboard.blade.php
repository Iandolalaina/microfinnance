<div class="max-w-md mx-auto px-4 py-6 pb-24">

    {{-- Bandeau de marque --}}
    <div class="sticky-page-header flex items-center justify-between">
        <p class="text-brand-600 font-bold tracking-wide text-sm">MITSINJO</p>
        <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-brand-600">Accueil</a>
    </div>

    {{-- En-tête avec nom du client et déconnexion --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-gray-500">Bonjour,</p>
            <h1 class="text-lg font-semibold text-gray-900">{{ auth()->user()->name }}</h1>
        </div>
        <a href="{{ url('/client/loans/create') }}" class="rounded-lg bg-brand-600 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-700">
            Demander un crédit
        </a>
        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-400 hover:text-red-600">
                Déconnexion
            </button>
        </form>
    </div>

    @if (session('member_matricule'))
        <div class="mb-5 rounded-xl border border-green-200 bg-green-50 p-4" role="status">
            <p class="text-sm font-medium text-green-800">Votre inscription est confirmée. Voici votre matricule :</p>
            <p class="mt-1 text-lg font-bold text-green-900">{{ session('member_matricule') }}</p>
            <p class="mt-1 text-xs text-green-700">Gardez-le pour vous connecter à votre espace.</p>
        </div>
    @endif

    @if ($pendingLoan)
        <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800" role="status">
            Votre demande de crédit {{ $pendingLoan->type?->name }} de {{ number_format($pendingLoan->amount, 0, ',', ' ') }} Ar est en cours d’examen.
        </div>
    @elseif ($rejectedLoan)
        <div class="mb-5 rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-700" role="status">
            Votre dernière demande de crédit a été refusée. Vous pouvez contacter votre agent pour en connaître le motif ou déposer une nouvelle demande.
        </div>
    @endif

    <section class="mb-6" aria-labelledby="announcements-heading">
        <div class="mb-3 flex items-center justify-between">
            <h2 id="announcements-heading" class="text-sm font-semibold text-gray-700">Informations de MITSINJO</h2>
            @if (config('webpush.vapid.public_key'))
                <button
                    type="button"
                    data-enable-push
                    data-vapid-public-key="{{ config('webpush.vapid.public_key') }}"
                    data-subscription-url="{{ route('push-subscriptions.store') }}"
                    class="text-xs font-medium text-brand-600 hover:text-brand-700"
                >Activer les notifications</button>
            @endif
        </div>
        <p data-push-status class="mb-3 text-xs text-gray-500" aria-live="polite">
            @if (! config('webpush.vapid.public_key'))
                Notifications push non configurées sur le serveur.
            @else
                Activez les notifications sur cet appareil pour recevoir les nouvelles informations hors ligne.
            @endif
        </p>
        <div class="space-y-2">
            @forelse ($announcements as $announcement)
                <article class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                    <div class="mb-1 flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-gray-900">{{ $announcement->title }}</h3>
                        <time class="shrink-0 text-xs text-gray-400" datetime="{{ $announcement->published_at->toIso8601String() }}">
                            {{ $announcement->published_at->format('d/m/Y') }}
                        </time>
                    </div>
                    <p class="whitespace-pre-line text-sm leading-6 text-gray-600">{{ $announcement->content }}</p>
                </article>
            @empty
                <p class="rounded-xl border border-gray-100 bg-white p-4 text-sm text-gray-500">Aucune information pour le moment.</p>
            @endforelse
        </div>
    </section>

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
                <div>
                    <p class="text-brand-100 text-xs mb-0.5">Intérêts totaux</p>
                    <p class="font-semibold">{{ number_format($interestAmount, 0, ',', ' ') }} Ar</p>
                </div>
                <div>
                    <p class="text-brand-100 text-xs mb-0.5">Taux d’intérêt</p>
                    <p class="font-semibold">{{ number_format($loan->interest_rate, 2, ',', ' ') }} %</p>
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
                    <a href="{{ $next instanceof \App\Models\LoanSchedule
                        ? url('/client/loan-payment/' . $next->id)
                        : url('/client/payment/' . $next->id) }}"
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
                        @if ($schedule instanceof \App\Models\LoanSchedule)
                            <p class="text-xs text-gray-500">
                                dont {{ number_format($schedule->interest_due, 0, ',', ' ') }} Ar d’intérêts
                            </p>
                        @endif
                    </div>

                    {{-- Badge de statut coloré selon l'état de l'échéance --}}
                    @php
                        $badgeClasses = match($schedule->effective_status) {
                            'paid' => 'bg-green-100 text-green-700',
                            'late' => 'bg-red-100 text-red-700',
                            'partial' => 'bg-amber-100 text-amber-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                        $badgeLabel = match($schedule->effective_status) {
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
