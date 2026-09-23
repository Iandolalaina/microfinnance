<div class="max-w-md mx-auto px-4 py-6">

    <p class="text-center text-brand-600 font-bold tracking-wide text-sm mb-6">MITSINJO</p>

    @if (! $paymentDone)
        {{-- ÉCRAN 1 : formulaire de paiement --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

            <a href="{{ url('/client/dashboard') }}" class="text-sm text-gray-400 mb-4 inline-block">
                ← Retour
            </a>

            <h1 class="text-lg font-semibold text-gray-900 mb-1">Payer une échéance</h1>
            <p class="text-sm text-gray-500 mb-5">
                Échéance du {{ \Carbon\Carbon::parse($schedule->due_date)->translatedFormat('d F Y') }}
            </p>

            <div class="bg-brand-50 rounded-xl p-4 text-center mb-5">
                <p class="text-xs text-brand-600 uppercase tracking-wide mb-1">Montant à payer</p>
                <p class="text-2xl font-bold text-brand-700">
                    {{ number_format($schedule->amount_due - $schedule->amount_paid, 0, ',', ' ') }} Ar
                </p>
            </div>

            <form wire:submit="pay" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Votre numéro Mvola
                    </label>
                    <input
                        type="tel"
                        wire:model="mvolaPhone"
                        placeholder="0343500003"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-base
                               focus:outline-none focus:ring-2 focus:ring-brand-500"
                    >
                    @error('mvolaPhone')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1.5">
                        Mode simulation : utilisez n'importe quel numéro à 10 chiffres pour tester.
                    </p>
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full bg-brand-600 hover:bg-brand-700 text-white font-medium py-3.5 rounded-xl
                           disabled:opacity-60"
                >
                    <span wire:loading.remove>Confirmer le paiement</span>
                    <span wire:loading>Traitement en cours...</span>
                </button>
            </form>
        </div>
    @else
        {{-- ÉCRAN 2 : confirmation après paiement réussi --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 text-center">
            <div class="mx-auto h-14 w-14 rounded-full bg-green-100 flex items-center justify-center mb-4">
                <span class="text-green-600 text-2xl">✓</span>
            </div>
            <h1 class="text-lg font-semibold text-gray-900 mb-1">Paiement confirmé</h1>
            <p class="text-sm text-gray-500 mb-5">
                {{ number_format($lastPayment->amount, 0, ',', ' ') }} Ar ont été enregistrés avec succès.
            </p>

            <a href="{{ url('/client/receipts/' . $lastPayment->id) }}"
               target="_blank"
               class="block w-full bg-brand-600 hover:bg-brand-700 text-white font-medium py-3 rounded-xl mb-3">
                Télécharger le reçu (PDF)
            </a>

            <a href="{{ url('/client/dashboard') }}"
               class="block w-full text-gray-500 text-sm py-2">
                Retour au tableau de bord
            </a>
        </div>
    @endif

</div>
