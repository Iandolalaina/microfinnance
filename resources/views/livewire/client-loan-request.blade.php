<div class="mx-auto max-w-3xl px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <p class="text-sm font-bold tracking-wide text-brand-600">MITSINJO — Crédit</p>
            <h1 class="mt-1 text-xl font-semibold text-gray-900">Demander un crédit</h1>
        </div>
        <a href="{{ url('/client/dashboard') }}" class="text-sm text-gray-500 hover:text-brand-600">Mon espace</a>
    </div>

    @if ($submitted)
        <div class="mb-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="status">
            Votre demande a été transmise. Un agent ou un administrateur l’étudiera avant toute activation du prêt.
        </div>
    @endif

    @if ($pendingRequest)
        <section class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-5">
            <h2 class="font-semibold text-amber-900">Une demande est en cours d’examen</h2>
            <p class="mt-1 text-sm text-amber-800">
                {{ $pendingRequest->type?->name }} — {{ number_format($pendingRequest->amount, 0, ',', ' ') }} Ar.
                Statut : en attente de validation.
            </p>
        </section>
    @else
        <form wire:submit="submit" class="space-y-5 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <div>
                <label for="loanTypeId" class="mb-1 block text-sm font-medium text-gray-700">Type de crédit</label>
                <select id="loanTypeId" wire:model.live="loanTypeId" required class="w-full rounded-xl border border-gray-300 px-4 py-3">
                    <option value="">Choisir un type</option>
                    @foreach ($loanTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
                @error('loan_type_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            @if ($selectedType)
                <div class="rounded-xl bg-gray-50 p-3 text-sm text-gray-600">
                    {{ $selectedType->description }}
                    @if ($selectedType->effective_max_amount !== null)
                        <p class="mt-1">Plafond : {{ number_format($selectedType->effective_max_amount, 0, ',', ' ') }} Ar</p>
                    @endif
                </div>
            @endif

            <div>
                <label for="groupId" class="mb-1 block text-sm font-medium text-gray-700">Groupe solidaire (facultatif)</label>
                <select id="groupId" wire:model="groupId" class="w-full rounded-xl border border-gray-300 px-4 py-3">
                    <option value="">Demande individuelle</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->members_count }} membres)</option>
                    @endforeach
                </select>
                @error('group_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="purpose" class="mb-1 block text-sm font-medium text-gray-700">Objet de la demande</label>
                <textarea id="purpose" wire:model="purpose" required maxlength="255" rows="3" class="w-full rounded-xl border border-gray-300 px-4 py-3"></textarea>
                @error('purpose') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="amount" class="mb-1 block text-sm font-medium text-gray-700">Montant demandé (Ar)</label>
                    <input id="amount" type="number" min="1000" step="1000" wire:model="amount" required class="w-full rounded-xl border border-gray-300 px-4 py-3">
                    @error('amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="durationMonths" class="mb-1 block text-sm font-medium text-gray-700">Durée (mois)</label>
                    <input id="durationMonths" type="number" min="{{ $selectedType?->min_duration_months ?? 1 }}" max="{{ $selectedType?->max_duration_months ?? 12 }}" wire:model="durationMonths" required class="w-full rounded-xl border border-gray-300 px-4 py-3">
                    @error('duration_months') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="repaymentFrequency" class="mb-1 block text-sm font-medium text-gray-700">Fréquence</label>
                    <select id="repaymentFrequency" wire:model="repaymentFrequency" required class="w-full rounded-xl border border-gray-300 px-4 py-3">
                        @foreach ($selectedType?->allowed_frequencies ?? [] as $frequency)
                            <option value="{{ $frequency }}">{{ ['weekly' => 'Hebdomadaire', 'biweekly' => 'Bimensuelle', 'monthly' => 'Mensuelle'][$frequency] ?? $frequency }}</option>
                        @endforeach
                    </select>
                    @error('repayment_frequency') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="gracePeriodDays" class="mb-1 block text-sm font-medium text-gray-700">Différé (jours)</label>
                    <input id="gracePeriodDays" type="number" min="0" max="120" wire:model="gracePeriodDays" @disabled(! $selectedType?->allows_grace_period) class="w-full rounded-xl border border-gray-300 px-4 py-3 disabled:bg-gray-100">
                    @error('grace_period_days') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <button type="submit" wire:loading.attr="disabled" class="w-full rounded-xl bg-brand-600 px-5 py-3 font-semibold text-white hover:bg-brand-700 disabled:opacity-60">
                Envoyer ma demande
            </button>
        </form>
    @endif
</div>
