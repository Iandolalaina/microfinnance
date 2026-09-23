<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'MITSINJO' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles {{-- Nécessaire : injecte le CSS interne de Livewire --}}
</head>
<body class="bg-gray-50 min-h-screen">

    {{ $slot }} {{-- C'est ICI que le contenu du composant Livewire s'affiche --}}

    @livewireScripts {{-- Nécessaire : injecte le JS qui fait fonctionner Livewire --}}
</body>
</html>
