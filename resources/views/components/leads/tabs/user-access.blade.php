@props(['selectedUsers' => [], 'userOptions' => [], 'tabErrors' => []])

<x-slot:label>
    <div class="flex items-center gap-1 whitespace-nowrap">
        @if($tabErrors['user-access'] ?? false)
            <x-mary-badge value="{{ $tabErrors['user-access'] }}" class="badge-error badge-sm"/>
        @endif
    </div>
</x-slot:label>

<div class="space-y-4">
    <div class="alert alert-info">
        <x-mary-icon name="o-information-circle" class="w-5 h-5" />
        <div>
            <p class="font-semibold">Comparte este lead con otros vendedores</p>
            <p class="text-sm">Selecciona los usuarios vendedores con los que deseas que este lead sea visible.</p>
        </div>
    </div>

    {{-- Selector múltiple de usuarios --}}
    <div>
{{--        <x-mary-checkbox-group--}}
{{--            label="Usuarios Vendedores"--}}
{{--            icon="o-user-group"--}}
{{--            wire:model="selectedUsers"--}}
{{--            :options="$userOptions"--}}
{{--            description="Selecciona los usuarios que tendrán acceso a este lead"--}}
{{--            :error="$errors->first('selectedUsers')"--}}
{{--        />--}}
        <x-mary-choices-offline
                label="Usuarios Vendedores"
                wire:model.live.debounce="selectedUsers"
                :options="$userOptions"
                placeholder="Selecciona los usuarios que tendrán acceso a este lead"
                no-result-text="No se encontraron usuarios"
                clearable
                searchable
                class="w-full select-primary "
        />
    </div>
    <br>
    <br>
    <br>
    <br><br>
    <br>
    <br>
    <br>

    {{-- Resumen de usuarios seleccionados --}}
    @if(empty($selectedUsers))
        <div class="alert alert-warning">
            <x-mary-icon name="o-exclamation-triangle" class="w-4 h-4" />
            <span>Sin usuarios seleccionados. Solo tú tendrás acceso a este lead.</span>
        </div>
    @endif

    {{-- Indicador de errores en la pestaña --}}
    @if($tabErrors['user_access'] ?? 0)
        <div class="alert alert-error">
            <x-mary-icon name="o-exclamation-triangle" class="w-4 h-4" />
            <span>Hay {{ $tabErrors['user_access'] }} error(es) en esta sección.</span>
        </div>
    @endif
</div>

