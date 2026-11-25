<x-mary-modal wire:model="conceptModal" title="Nuevo Concepto" separator>

    <div class="space-y-4">
        <x-mary-input
            wire:model="conceptForm.name"
            label="Nombre del concepto"
            placeholder="Ej. Instalación, Flete, Servicio técnico..."
            required
        />

        <x-mary-textarea
            wire:model="conceptForm.description"
            label="Descripción"
            rows="2"
            placeholder="Descripción opcional..."
        />

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-mary-input
                wire:model="conceptForm.unit_price"
                type="number"
                label="Precio unitario (USD)"
                min="0"
                step="0.01"
                required
            />
            <x-mary-input
                wire:model="conceptForm.quantity"
                type="number"
                label="Cantidad"
                min="1"
                step="1"
            />
            <x-mary-input
                wire:model="conceptForm.discount"
                type="number"
                label="Descuento %"
                min="0"
                max="100"
            />
        </div>
    </div>

    <x-slot:actions>
        <x-mary-button
            label="Cancelar"
            wire:click="$set('conceptModal', false)"
            class="btn-ghost"
        />
        <x-mary-button
            label="Agregar concepto"
            wire:click="addConcept"
            class="btn-primary"
            spinner="addConcept"
        />
    </x-slot:actions>

</x-mary-modal>
