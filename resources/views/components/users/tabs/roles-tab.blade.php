@props(['roles' => [], 'optionsRoles' => [], 'tabErrors' => []])

<div>
    <x-slot:label>
        <div class="flex items-center gap-1 whitespace-nowrap">
            <x-mary-icon name="o-lock-closed" class="w-4 h-4" />
            <span>Roles</span>
            @if($tabErrors['roles'] ?? false)
                <x-mary-badge value="{{ $tabErrors['roles'] }}" class="badge-error badge-sm"/>
            @endif
        </div>
    </x-slot:label>

    <div class="p-1">
        <x-mary-choices-offline
                label="Selecciona el rol del usuario"
                wire:model="roles"
                :options="$optionsRoles"
                placeholder="Buscar rol..."
                no-result-text="No se encontraron roles"
                clearable
                searchable
                required
                class="w-full select-primary "
        />

{{--        <p class="text-xs text-gray-500 mt-2">--}}
{{--            <x-mary-icon name="o-information-circle" class="w-3 h-3 inline" />--}}
{{--            Puedes seleccionar múltiples roles--}}
{{--        </p>--}}
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
    </div>
</div>