<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - MITSINJO</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-sm">

        {{-- Logo / Titre --}}
        <div class="text-center mb-8">
            <div class="mx-auto h-14 w-14 rounded-full bg-brand-600 flex items-center justify-center mb-3">
             
            </div>
            <h1 class="text-xl font-semibold text-gray-900">MITSINJO</h1>
            <p class="text-sm text-gray-500 mt-1">Connectez-vous à votre espace</p>
        </div>

        {{-- Bloc d'erreurs (affiché seulement s'il y a des erreurs de validation) --}}
        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3">
                <p class="text-sm text-red-700">{{ $errors->first() }}</p>
            </div>
        @endif

        <form method="POST" action="{{ url('/login') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
            @csrf {{-- Jeton de sécurité obligatoire pour tout formulaire Laravel --}}

            {{-- Numéro de téléphone --}}
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Numéro de téléphone
                </label>
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    value="{{ old('phone') }}"
                    placeholder="034 00 000 00"
                    required
                    autofocus
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 text-base
                           focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                >
            </div>

            {{-- Mot de passe --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Mot de passe
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="••••••••"
                    required
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 text-base
                           focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                >
            </div>

            {{-- Se souvenir de moi --}}
            <div class="flex items-center">
                <input type="checkbox" id="remember" name="remember"
                       class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                <label for="remember" class="ml-2 text-sm text-gray-600">Rester connecté</label>
            </div>

            {{-- Bouton de soumission --}}
            <button
                type="submit"
                class="w-full bg-brand-600 hover:bg-brand-700 text-white font-medium
                       py-3.5 rounded-xl text-base transition-colors active:scale-[0.98]"
            >
                Se connecter
            </button>
        </form>

        <p class="text-center text-xs text-gray-400 mt-6">
            Besoin d'aide ? Contactez votre agent ONG.
        </p>
    </div>

</body>
</html>
