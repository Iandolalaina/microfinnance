<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Découvrez les types de crédit proposés par MITSINJO et soumettez une demande à votre agent.">
    <title>Nos crédits — MITSINJO</title>
    @include('partials.pwa')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-800">
    <header class="border-b border-gray-100 bg-white">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4" aria-label="Navigation principale">
            <a href="{{ route('home') }}" class="font-bold tracking-wide text-brand-600">MITSINJO</a>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('savings.index') }}" class="text-gray-600 hover:text-brand-600">Épargne</a>
                <a href="{{ route('home') }}#services" class="text-gray-600 hover:text-brand-600">Nos services</a>
                <a href="{{ auth()->check() ? url('/client/dashboard') : route('login') }}" class="font-semibold text-brand-600">
                    {{ auth()->check() ? 'Mon espace' : 'Se connecter' }}
                </a>
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-12">
        <section class="mx-auto mb-10 max-w-3xl text-center">
            <p class="text-sm font-bold uppercase tracking-widest text-brand-600">Nos solutions de financement</p>
            <h1 class="mt-3 text-3xl font-bold text-gray-900 sm:text-4xl">Un crédit adapté à votre projet</h1>
            <p class="mt-4 text-gray-600">
                Choisissez la catégorie correspondant à votre besoin. Votre demande sera étudiée par un agent ou un administrateur avant toute décision.
            </p>
        </section>

        @php
            $creditPhotos = [
                'AGR' => [
                    'path' => 'images/credits/credit-activite-generatrice-revenus.jpg',
                    'alt' => 'Commerce ou activité artisanale',
                ],
                'AGRI_ELEVAGE' => [
                    'path' => 'images/credits/credit-agricole-elevage.jpg',
                    'alt' => 'Activité agricole ou élevage',
                ],
                'SOCIAL_URGENCE' => [
                    'path' => 'images/credits/credit-social-urgence.jpg',
                    'alt' => 'Soutien à une dépense familiale urgente',
                ],
            ];
        @endphp

        <section class="flex flex-col gap-6" aria-label="Types de crédit">
            @forelse ($loanTypes as $type)
                @php($photo = $creditPhotos[$type->code] ?? null)
                @php($photoExists = $photo && file_exists(public_path($photo['path'])))
                <article class="grid overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm md:grid-cols-2">
                    <div class="flex min-h-64 items-center justify-center bg-gray-100 md:min-h-full">
                        @if ($photoExists)
                            <img src="{{ asset($photo['path']) }}" alt="{{ $photo['alt'] }}" class="h-full min-h-64 w-full object-cover">
                        @else
                            <p class="px-6 text-center text-sm text-gray-500">
                                Photo à ajouter :
                                <code class="mt-2 block break-all text-xs text-gray-700">public/{{ $photo['path'] ?? 'images/credits/credit-' . \Illuminate\Support\Str::slug($type->name) . '.jpg' }}</code>
                            </p>
                        @endif
                    </div>
                    <div class="flex flex-col p-6 sm:p-8">
                        <h2 class="text-xl font-semibold text-gray-900">{{ $type->name }}</h2>
                        <p class="mt-3 flex-1 text-sm leading-6 text-gray-600">{{ $type->description }}</p>
                        <dl class="mt-5 space-y-2 border-t border-gray-100 pt-4 text-sm">
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500">Durée</dt>
                                <dd class="text-right font-medium">{{ $type->min_duration_months }}–{{ $type->max_duration_months }} mois</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500">Remboursement</dt>
                                <dd class="text-right font-medium">{{ collect($type->allowed_frequencies)->map(fn ($frequency) => ['weekly' => 'Hebdomadaire', 'biweekly' => 'Bimensuel', 'monthly' => 'Mensuel'][$frequency] ?? $frequency)->join(', ') }}</dd>
                            </div>
                            @if ($type->effective_max_amount !== null)
                                <div class="flex justify-between gap-3">
                                    <dt class="text-gray-500">Plafond</dt>
                                    <dd class="text-right font-medium">{{ number_format($type->effective_max_amount, 0, ',', ' ') }} Ar</dd>
                                </div>
                            @endif
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500">Taux indicatif</dt>
                                <dd class="text-right font-medium">{{ number_format($type->effective_default_interest_rate, 2, ',', ' ') }} % / mois</dd>
                            </div>
                        </dl>
                        <a href="{{ auth()->check() && auth()->user()->role === 'client' ? url('/client/loans/create') : (auth()->check() ? route('home') : route('login')) }}"
                           class="mt-6 inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                            {{ auth()->check() && auth()->user()->role === 'client' ? 'Demander ce crédit' : 'Contacter / se connecter' }}
                        </a>
                    </div>
                </article>
            @empty
                <p class="rounded-xl bg-white p-6 text-center text-gray-500">Aucun type de crédit n’est disponible pour le moment.</p>
            @endforelse
        </section>

        @if (config('loans.rates_are_provisional'))
            <p class="mx-auto mt-6 max-w-3xl text-center text-xs text-gray-500">
                Les taux affichés sont indicatifs et provisoires ; ils restent soumis à la confirmation de la gouvernance VAHATRA.
            </p>
        @endif
    </main>
</body>
</html>
