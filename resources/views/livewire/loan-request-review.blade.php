<div class="flex shrink-0 gap-2">
    <button type="button" wire:click="approve" wire:confirm="Confirmer l’approbation et l’activation de ce prêt ?" class="rounded-lg bg-green-600 px-3 py-2 text-xs font-semibold text-white hover:bg-green-700">
        Valider
    </button>
    <button type="button" wire:click="reject" wire:confirm="Confirmer le rejet de cette demande ?" class="rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">
        Refuser
    </button>
</div>
