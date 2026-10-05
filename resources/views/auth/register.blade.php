<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devenir membre - MITSINJO</title>
    @include('partials.pwa')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen px-4 py-10">
    <main class="mx-auto w-full max-w-2xl">
        <a href="{{ route('home') }}" class="mb-6 block text-center text-sm text-gray-500 hover:text-brand-600">← Accueil MITSINJO</a>

        <header class="mb-8 text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-brand-600">
                <span class="text-2xl font-bold text-white">M</span>
            </div>
            <h1 class="text-xl font-semibold text-gray-900">Devenir membre</h1>
            <p class="mt-1 text-sm text-gray-500">Créez votre compte pour accéder à votre espace personnel.</p>
        </header>

        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3" role="alert">
                <p class="text-sm text-red-700">{{ $errors->first() }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="space-y-5 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">Nom complet</label>
                    <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" required maxlength="150" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label for="phone" class="mb-1.5 block text-sm font-medium text-gray-700">Téléphone</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" required maxlength="20" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label for="cin" class="mb-1.5 block text-sm font-medium text-gray-700">Numéro CIN</label>
                    <input id="cin" name="cin" type="text" inputmode="numeric" pattern="[0-9]{12}" maxlength="12" value="{{ old('cin') }}" required class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700">E-mail <span class="font-normal text-gray-400">(facultatif)</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label for="region" class="mb-1.5 block text-sm font-medium text-gray-700">Région</label>
                    <input id="region" name="region" value="{{ old('region') }}" required maxlength="150" autocomplete="address-level1" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label for="fokontany" class="mb-1.5 block text-sm font-medium text-gray-700">Fokontany</label>
                    <input id="fokontany" name="fokontany" value="{{ old('fokontany') }}" required maxlength="150" autocomplete="address-level3" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label for="profile_photo" class="mb-1.5 block text-sm font-medium text-gray-700">Photo de profil</label>
                    <input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:font-medium">
                    <p class="mt-1 text-xs text-gray-400">JPG, PNG ou WEBP, 2 Mo maximum.</p>
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700">Mot de passe</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required minlength="8" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required minlength="8" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <button type="submit" class="w-full rounded-xl bg-brand-600 py-3.5 text-base font-medium text-white transition-colors hover:bg-brand-700 active:scale-[0.98]">Créer mon compte membre</button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">Déjà membre ? <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:text-brand-700">Se connecter</a></p>
    </main>
</body>
</html>