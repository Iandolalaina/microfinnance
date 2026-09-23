<div class="max-w-md mx-auto px-4 py-6">

    <p class="text-center text-brand-600 font-bold tracking-wide text-sm mb-6">MITSINJO — Saisie manuelle</p>

    @if (! $done)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

            <a href="{{ url('/agent/dashboard') }}" class="text-sm text-gray-400 mb-4 inline-block">← Retour</a>

            <h1 class="text-lg font-semibold text-gray-900 mb-1">Versement en espèces</h1>
            <p class="text-sm text-gray-500 mb-5">
                Client : <span class="font-medium">{{ $schedule->loan->client->name }}</span><br>
                Échéance du {{ \Carbon\Carbon::parse($schedule->due_date)->translatedFormat('d F Y') }}
            </p>

            <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 text-xs text-amber-700 mb-5">
                Montant initialement dû : {{ number_format($schedule->amount_due - $schedule->amount_paid, 0, ',', ' ') }} Ar.
                Ajustez si le client n'a versé qu'une partie.
            </div>

            <form wire:submit="record" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Montant reçu (Ar)
                    </label>
                    <input
                        type="number"
                        wire:model="amount"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-base
                               focus:outline-none focus:ring-2 focus:ring-brand-500"
                    >
                    @error('amount')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full bg-brand-600 hover:bg-brand-700 text-white font-medium py-3.5 rounded-xl disabled:opacity-60"
                >
                    <span wire:loading.remove>Enregistrer le versement</span>
                    <span wire:loading>Enregistrement...</span>
                </button>
            </form>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 text-center">
            <div class="mx-auto h-14 w-14 rounded-full bg-green-100 flex items-center justify-center mb-4">
                <span class="text-green-600 text-2xl">✓</span>
            </div>
            <h1 class="text-lg font-semibold text-gray-900 mb-1">Versement enregistré</h1>
            <p class="text-sm text-gray-500 mb-5">
                {{ number_format($lastPayment->amount, 0, ',', ' ') }} Ar ont été enregistrés
                pour {{ $lastPayment->client->name }}.
            </p>

            <a href="{{ url('/agent/receipts/' . $lastPayment->id) }}"
               target="_blank"
               class="block w-full bg-brand-600 hover:bg-brand-700 text-white font-medium py-3 rounded-xl mb-3">
                Télécharger le reçu (PDF)
            </a>

            <a href="{{ url('/agent/dashboard') }}" class="block w-full text-gray-500 text-sm py-2">
                Retour au tableau de bord
            </a>
        </div>
    @endif

</div>
