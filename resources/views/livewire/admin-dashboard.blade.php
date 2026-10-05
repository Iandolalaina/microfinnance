<div class="max-w-4xl mx-auto px-4 py-6 pb-16">

    {{-- En-tête --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-brand-600 font-bold tracking-wide text-sm">MITSINJO — Administration</p>
            <h1 class="text-lg font-semibold text-gray-900 mt-1">Bonjour, {{ auth()->user()->name }}</h1>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-brand-600">Accueil</a>
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
