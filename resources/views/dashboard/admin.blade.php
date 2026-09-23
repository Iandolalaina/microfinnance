<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Administrateur - MITSINJO</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen p-6">
    <div class="max-w-2xl mx-auto">
        <p class="text-center text-brand-600 font-bold tracking-wide text-sm mb-4">MITSINJO</p>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h1 class="text-xl font-semibold text-gray-900 mb-2">
                Bienvenue, {{ auth()->user()->name }} 👋
            </h1>
            <p class="text-sm text-gray-500 mb-6">
                Vous êtes connecté en tant qu'<span class="font-medium text-brand-600">Administrateur</span>.
            </p>

            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit" class="text-sm text-red-600 hover:underline">
                    Se déconnecter
                </button>
            </form>
        </div>
    </div>
</body>
</html>
