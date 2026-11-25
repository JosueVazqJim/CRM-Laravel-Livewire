@props([
    'purchaseOrderModalOpen',
    'poTotalMxn',
    'poAdvanceAmount' => null,
])

<x-mary-modal wire:model="purchaseOrderModalOpen" title="Orden de Compra" box-class="w-11/12 max-w-2xl">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-mary-datetime
            label="Fecha de anticipo"
            wire:model="poAdvancePaidAt"
            type="date"
            class="input-primary"
        />

        <x-mary-input
            label="Monto (MXN)"
            wire:model.live.debounce.500ms="poAdvanceAmount"
            type="number"
            step="0.01"
            min="0"
            class="input-primary"
        />

        <x-mary-input
            label="Medio de pago"
            wire:model="poPaymentMethod"
            class="input-primary"
        />

        <x-mary-input
            label="Referencia"
            wire:model="poReference"
            class="input-primary"
        />

        <div>
            <label class="text-sm text-gray-500">Restan</label>
            <p class="font-medium text-lg">
                @if($poTotalMxn !== null)
                    {{ number_format($poTotalMxn - (float) ($poAdvanceAmount ?? 0), 2) }} MXN
                @else
                    N/A
                @endif
            </p>
        </div>

        <x-mary-datetime
            label="Fecha de liquidación"
            wire:model="poSettlementDate"
            type="date"
            class="input-primary"
        />
    </div>

    <x-slot:actions>
        <x-mary-button label="Cancelar" wire:click="$set('purchaseOrderModalOpen', false)" class="btn-outline" />
        <x-mary-button label="Guardar" wire:click="savePurchaseOrder" class="btn-primary" spinner="savePurchaseOrder" />
    </x-slot:actions>
</x-mary-modal>
