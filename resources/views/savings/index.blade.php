<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Épargne — MITSINJO</title>
    @include('partials.pwa')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-800">
    <header class="sticky top-0 z-50 border-b border-gray-100 bg-white">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4" aria-label="Navigation principale">
            <a href="{{ route('home') }}" class="font-bold tracking-wide text-brand-600">MITSINJO</a>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('credits.index') }}" class="text-gray-600 hover:text-brand-600">Crédit</a>
                <a href="{{ route('home') }}" class="text-gray-600 hover:text-brand-600">Accueil</a>
            </div>
        </nav>
    </header>
    <main class="mx-auto max-w-3xl px-4 py-16">
        <section class="rounded-2xl border border-gray-100 bg-white p-8 text-center shadow-sm">
            <p class="text-sm font-bold uppercase tracking-widest text-brand-600">Nos services</p>
            <h1 class="mt-3 text-3xl font-bold text-gray-900">Épargne</h1>
            <p class="mx-auto mt-4 max-w-xl leading-7 text-gray-600">
                Pour connaître les modalités d’épargne proposées par votre institution, contactez votre agent MITSINJO ou rendez-vous à votre agence.
                Les opérations d’épargne ne sont pas encore disponibles dans cet espace en ligne.
            </p>
            <a href="{{ route('home') }}" class="mt-7 inline-flex rounded-xl bg-brand-600 px-5 py-3 font-semibold text-white hover:bg-brand-700">
                Retour à l’accueil
            </a>
        </section>
    </main>
</body>
</html>
