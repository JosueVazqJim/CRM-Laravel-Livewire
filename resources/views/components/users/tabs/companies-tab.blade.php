@props(['companies' => [], 'companyOptions' => [], 'tabErrors' => []])

<div>
    <x-slot:label>
        <div class="flex items-center gap-1 whitespace-nowrap">
            <x-mary-icon name="o-building-office" class="w-4 h-4" />
            <span>Empresas</span>
            @if($tabErrors['companies'] ?? false)
                <x-mary-badge value="{{ $tabErrors['companies'] }}" class="badge-error badge-sm"/>
            @endif
        </div>
    </x-slot:label>

    <div class="p-1">
        <x-mary-choices-offline
                label="Selecciona las empresas que administra"
                wire:model="companies"
                :options="$companyOptions"
                placeholder="Buscar empresa..."
                no-result-text="No se encontraron empresas"
                clearable
                searchable
                required
                class="w-full select-primary "
        />

        <p class="text-xs text-gray-500 mt-2">
            <x-mary-icon name="o-information-circle" class="w-3 h-3 inline" />
            Puedes seleccionar múltiples empresas
        </p>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
    </div>
</div>