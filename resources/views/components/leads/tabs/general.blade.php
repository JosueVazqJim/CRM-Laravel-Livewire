@props(['company_id' => 0, 'name_company' => '', 'state_id' => 0, 'last_contact' => '', 'companyOptions' => [], 'states' => [], 'tabErrors' => []])

<x-slot:label>
    <div class="flex items-center gap-1 whitespace-nowrap">
        @if($tabErrors['general'] ?? false)
            <x-mary-badge value="{{ $tabErrors['general'] }}" class="badge-error badge-sm"/>
        @endif
    </div>
</x-slot:label>

<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Empresa --}}
        <div>
{{--            <x-mary-select--}}
{{--                label="Empresa"--}}
{{--                icon="o-building-office-2"--}}
{{--                wire:model="company_id"--}}
{{--                :options="collect($companyOptions)->map(fn($c) => ['id' => $c['id'], 'name' => $c['name']])->toArray()"--}}
{{--                :error="$errors->first('company_id')"--}}
{{--            />--}}

            <x-mary-choices-offline
                    label="Selecciona la empresa del corporativo al que pertenece este cliente."
                    wire:model="company_id"
                    wire:model.live.debounce="company"
                    :options="$companyOptions"
                    placeholder="Buscar empresa..."
                    no-result-text="No se encontraron empresas"
                    single
                    searchable
                    required
                    class="w-full select-primary"
            />
        </div>

        {{-- Nombre de la Empresa Cliente --}}
        <div>
            <x-mary-input
                label="Nombre de la Empresa Cliente"
                icon="o-building-office"
                wire:model="name_company"
                placeholder="Ej. Acme Corp"
                :error="$errors->first('name_company')"
                required
                class="input-primary"
            />
        </div>

        {{-- Estado --}}
        <div class="md:col-span-2">
            <x-mary-select
                label="Estado"
                icon="o-map-pin"
                wire:model="state_id"
                :options="$states"
                option-value="id"
                option-label="name"
                placeholder="Selecciona un estado..."
                :error="$errors->first('state_id')"
                class="select-primary"
                searchable
                clearable
            />
        </div>

        <div class="md:col-span-2">
            <x-mary-datetime
                label="Fecha de último contacto"
                icon="o-calendar"
                wire:model="last_contact"
                type="datetime-local"
                :error="$errors->first('last_contact')"
                class="input-primary"
            />
        </div>
    </div>

    {{-- Indicador de errores en la pestaña --}}
    @if($tabErrors['general'] ?? 0)
        <div class="alert alert-warning">
            <x-mary-icon name="o-exclamation-triangle" class="w-4 h-4" />
            <span>Hay {{ $tabErrors['general'] }} campo(s) con error en esta sección.</span>
        </div>
    @endif
</div>

