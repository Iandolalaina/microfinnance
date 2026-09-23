<div class="max-w-md mx-auto px-4 py-6">

    <p class="text-center text-brand-600 font-bold tracking-wide text-sm mb-6">MITSINJO — Annonces</p>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

        <a href="{{ url('/agent/dashboard') }}" class="text-sm text-gray-400 mb-4 inline-block">← Retour</a>

        <h1 class="text-lg font-semibold text-gray-900 mb-5">Publier une annonce</h1>

        @if ($published)
            <div class="bg-green-50 border border-green-100 rounded-xl p-4 mb-5 text-sm text-green-700">
                ✓ Annonce publiée avec succès.
            </div>
        @endif

        <form wire:submit="publish" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Titre</label>
                <input
                    type="text"
                    wire:model="title"
                    placeholder="Ex : Réunion d'information samedi"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-brand-500"
                >
                @error('title') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Message</label>
                <textarea
                    wire:model="content"
                    rows="4"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-brand-500"
                ></textarea>
                @error('content') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Zone ciblée</label>
                <select
                    wire:model="zoneId"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-brand-500"
                >
                    <option value="">Toutes les zones</option>
                    @foreach ($zones as $zone)
                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center">
                <input type="checkbox" wire:model="sendSms" id="sendSms"
                       class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                <label for="sendSms" class="ml-2 text-sm text-gray-600">
                    Envoyer aussi par SMS
                </label>
            </div>
            <p class="text-xs text-gray-400 -mt-2">
                (L'envoi SMS réel sera activé à la prochaine étape du projet)
            </p>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="w-full bg-brand-600 hover:bg-brand-700 text-white font-medium py-3.5 rounded-xl disabled:opacity-60"
            >
                Publier l'annonce
            </button>
        </form>
    </div>

</div>
