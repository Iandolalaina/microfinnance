<div class="max-w-6xl mx-auto px-4 py-6 pb-16">

    @php
        $isAdmin = auth()->user()->role === 'admin';
        $dashboardUrl = $isAdmin ? url('/admin/dashboard') : url('/agent/dashboard');
        $frequencyLabels = [
            'weekly' => 'Hebdomadaire',
            'biweekly' => 'Bimensuelle',
            'monthly' => 'Mensuelle',
        ];
    @endphp

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <p class="text-brand-600 font-bold tracking-wide text-sm">MITSINJO - Prets</p>
            <h1 class="text-lg font-semibold text-gray-900 mt-1">Octroi de pret</h1>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-brand-600">Accueil</a>
            <a href="{{ $dashboardUrl }}" class="text-sm text-gray-400">Retour</a>
        </div>
    </div>

    @if ($created)
        <div class="mb-5 rounded-xl border border-green-100 bg-green-50 p-4 text-sm text-green-700" role="status">
            Pret cree avec succes. Contrat : {{ $createdContractNumber }}.
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[420px_1fr]">
        <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 h-fit">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Parametres du pret</h2>

            <form wire:submit="create" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Membre emprunteur</label>
                    <select wire:model.live="userId" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Choisir un membre</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }} - {{ $client->phone }}</option>
                        @endforeach
                    </select>
                    @error('userId') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Groupe solidaire</label>
                    <select wire:model.live="groupId" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Pret individuel</option>
                        @foreach ($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->members_count }} membres)</option>
                        @endforeach
                    </select>
                    @error('groupId') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-400">Un groupe solidaire doit compter 3 a 5 membres.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Type de credit</label>
                    <select wire:model.live="loanTypeId" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Choisir un type</option>
                        @foreach ($loanTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('loanTypeId') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                @if ($selectedType)
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-3 text-xs leading-5 text-gray-500">
                        {{ $selectedType->description }}
                        @if ($selectedType->effective_max_amount !== null)
                            <p class="mt-1">Plafond : {{ number_format($selectedType->effective_max_amount, 0, ',', ' ') }} Ar</p>
                        @endif
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Objet du pret</label>
                    <input type="text" wire:model.live="purpose" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('purpose') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Montant</label>
                        <input type="number" min="1000" step="1000" wire:model.live="amount" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        @error('amount') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Taux %</label>
                        <input type="number" min="0" max="100" step="0.01" wire:model.live="interestRate" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        @error('interestRate') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Duree mois</label>
                        <input type="number" min="1" max="36" wire:model.live="durationMonths" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        @error('durationMonths') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Frequence</label>
                        <select wire:model.live="repaymentFrequency" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            @foreach ($frequencyLabels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('repaymentFrequency') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Premiere echeance</label>
                        <input type="date" wire:model.live="firstDueDate" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        @error('firstDueDate') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Grace jours</label>
                        <input type="number" min="0" max="120" wire:model.live="gracePeriodDays" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        @error('gracePeriodDays') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <button type="submit" wire:loading.attr="disabled" class="w-full rounded-xl bg-brand-600 py-3.5 font-medium text-white hover:bg-brand-700 disabled:opacity-60">
                    Valider et generer l'echeancier
                </button>
            </form>
        </section>

        <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Previsualisation echeancier</h2>
                    <p class="text-xs text-gray-400 mt-1">{{ $preview->count() }} echeance(s)</p>
                </div>
                <p class="mb-4 text-xs text-gray-500">
                    Intérêt simple : capital × taux mensuel × durée (en mois).
                    @if (config('loans.rates_are_provisional'))
                        Les taux affichés sont provisoires et restent à confirmer par la gouvernance VAHATRA.
                    @endif
                </p>
                <div class="text-right">
                    <p class="text-xs text-gray-400">Total a rembourser</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($totalPreview, 0, ',', ' ') }} Ar</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-medium uppercase tracking-wide text-gray-400">
                            <th class="py-2 pr-3">#</th>
                            <th class="py-2 pr-3">Date</th>
                            <th class="py-2 pr-3 text-right">Capital</th>
                            <th class="py-2 pr-3 text-right">Interet</th>
                            <th class="py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($preview as $line)
                            <tr>
                                <td class="py-2 pr-3 text-gray-500">{{ $line['installment_number'] }}</td>
                                <td class="py-2 pr-3 text-gray-700">{{ \Carbon\Carbon::parse($line['due_date'])->format('d/m/Y') }}</td>
                                <td class="py-2 pr-3 text-right text-gray-600">{{ number_format($line['principal_due'], 0, ',', ' ') }}</td>
                                <td class="py-2 pr-3 text-right text-gray-600">{{ number_format($line['interest_due'], 0, ',', ' ') }}</td>
                                <td class="py-2 text-right font-medium text-gray-900">{{ number_format($line['amount_due'], 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
