@props(['modules' => [], 'modulesOptions' => [], 'tabErrors' => []])

<div>
    <x-slot:label>
        <div class="flex items-center gap-1 whitespace-nowrap">
            <x-mary-icon name="o-briefcase" class="w-4 h-4" />
            <span>Módulos</span>
            @if($tabErrors['modules'] ?? false)
                <x-mary-badge value="{{ $tabErrors['modules'] }}" class="badge-error badge-sm"/>
            @endif
        </div>
    </x-slot:label>

    <div class="p-1">
        <x-mary-choices-offline
                label="Selecciona los módulos disponibles para esta empresa"
                wire:model="modules"
                :options="$modulesOptions"
                placeholder="Buscar módulo..."
                no-result-text="No se encontraron módulos"
                clearable
                searchable
                class="w-full select-primary "
        />

        <p class="text-xs text-gray-500 mt-2">
            <x-mary-icon name="o-information-circle" class="w-3 h-3 inline" />
            Puedes seleccionar múltiples módulos
        </p>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
    </div>
</div>