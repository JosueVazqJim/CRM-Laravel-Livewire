@props(['address' => [],
'states' => [],
'municipalities' => [],
'loadingMunicipalities' => false,
'tabErrors' => []])

<div>
    <x-slot:label>
        <div class="flex items-center gap-1 whitespace-nowrap">
            <x-mary-icon name="o-map-pin" class="w-4 h-4" />
            <span>Dirección</span>
            @if($tabErrors['address'] ?? false)
                <x-mary-badge value="{{ $tabErrors['address'] }}" class="badge-error badge-sm"/>
            @endif
        </div>
    </x-slot:label>

    <div class="p-1">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-mary-input label="Calle" wire:model="address.street" placeholder="Nombre de la calle" required class="input-primary"/>

            <x-mary-input label="No. Exterior" wire:model="address.exterior_number" required class="input-primary"/>
            <x-mary-input label="No. Interior" wire:model="address.interior_number" optional class="input-primary"/>

            <div class="sm:col-span-2">
                <x-mary-input label="Colonia" wire:model="address.colony" required class="input-primary"/>
            </div>

            {{-- Estado - SELECT con búsqueda --}}
            <x-mary-select
                    label="Estado"
                    wire:model.live="address.state"
                    :options="$states"
                    placeholder="Selecciona un estado"
                    option-value="id"
                    option-label="name"
                    class="select-primary w-full"
                    required
                    searchable
            />

            {{-- Municipio - SELECT con indicador de carga --}}
            <x-mary-select
                    label="Municipio"
                    wire:model="address.municipality"
                    :options="$municipalities"
                    :placeholder="empty($address->municipality)
                    ? ($loadingMunicipalities ? 'Cargando municipios...' : 'Selecciona un municipio')
                    : null"
                    :disabled="empty($municipalities) || $loadingMunicipalities"
                    class="select-primary w-full"
                    option-value="id"
                    option-label="name"
                    required
                    searchable
            >
            </x-mary-select>

            <x-mary-input label="Código Postal" wire:model="address.postal_code" required class="input-primary"/>
        </div>
    </div>
</div>