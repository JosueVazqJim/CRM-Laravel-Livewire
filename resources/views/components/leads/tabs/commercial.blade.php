@props(['urgency_level' => '', 'budget' => 0, 'undermine_channel' => '', 'classification' => '', 'funnel_stage' => '', 'probability' => 0, 'tabErrors' => []])
<x-slot:label>
    <div class="flex items-center gap-1 whitespace-nowrap">
        @if($tabErrors['commercial'] ?? false)
            <x-mary-badge value="{{ $tabErrors['commercial'] }}" class="badge-error badge-sm"/>
        @endif
    </div>
</x-slot:label>

<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Nivel de Urgencia --}}
        <div>
            <x-mary-select
                label="Nivel de Urgencia"
                placeholder="Selecciona el nivel de urgencia"
                icon="o-bolt"
                wire:model="urgency_level"
                :options="[
                    ['id' => 'baja', 'name' => 'Baja'],
                    ['id' => 'media', 'name' => 'Media'],
                    ['id' => 'alta', 'name' => 'Alta'],
                    ['id' => 'inmediata', 'name' => 'Inmediata'],
                ]"
                :error="$errors->first('urgency_level')"
                class="input-primary"
                required
            />
        </div>

        {{-- Presupuesto --}}
        <div>
            <x-mary-input
                label="Presupuesto"
                icon="o-banknotes"
                type="number"
                wire:model="budget"
                placeholder="0"
                min="0"
                :error="$errors->first('budget')"
                class="input-primary"
            />
        </div>

        {{-- Canal de Obtención --}}
        <div>
            <x-mary-select
                    label="Canal de Obtención"
                    icon="o-megaphone"
                    wire:model="undermine_channel"
                    :options="[
                        ['id' => 'página web', 'name' => 'Página web'],
                        ['id' => 'meta', 'name' => 'Meta'],
                        ['id' => 'venta fría', 'name' => 'Venta fría'],
                    ]"
                    :error="$errors->first('undermine_channel')"
                    class="input-primary"
                    required
                    placeholder="Selecciona el canal de obtención"
            />
        </div>

        {{-- Clasificación --}}
        <div>
{{--            <x-mary-input--}}
{{--                label="Clasificación"--}}
{{--                icon="o-tag"--}}
{{--                wire:model="classification"--}}
{{--                placeholder="Ej. Prospecto, Cliente Potencial"--}}
{{--                :error="$errors->first('classification')"--}}
{{--            />--}}
            <x-mary-select
                    label="Clasificación"
                    icon="o-tag"
                    wire:model="classification"
                    :options="[
                        ['id' => 'activo', 'name' => 'Activo'],
                        ['id' => 'descartado', 'name' => 'Descartado'],
                        ['id' => 'reciclado', 'name' => 'Reciclado'],
                    ]"
                    :error="$errors->first('undermine_channel')"
                    class="input-primary"
            />
        </div>

        {{-- Etapa del Embudo --}}
        <div>
            <x-mary-select
                label="Etapa del Embudo"
                icon="o-arrow-trending-down"
                wire:model="funnel_stage"
                :options="[
                    ['id' => 'ventas', 'name' => 'Ventas'],
                    ['id' => 'posventa', 'name' => 'Posventa'],
                    ['id' => 'fidelización', 'name' => 'Fidelización'],
                    ['id' => 'reactivación', 'name' => 'Reactivación'],
                ]"
                :error="$errors->first('funnel_stage')"
                class="input-primary"
                placeholder="Selecciona la etapa del embudo"
                required
            />
        </div>
    </div>

    {{-- Indicador de errores en la pestaña --}}
    @if($tabErrors['commercial'] ?? 0)
        <div class="alert alert-warning">
            <x-mary-icon name="o-exclamation-triangle" class="w-4 h-4" />
            <span>Hay {{ $tabErrors['commercial'] }} campo(s) con error en esta sección.</span>
        </div>
    @endif
</div>

