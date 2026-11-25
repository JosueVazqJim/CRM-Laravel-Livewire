<x-mary-card class="shadow-xl">
    <x-slot:title>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-mary-icon name="o-briefcase" class="h-5 w-5" />
                <span>Información Comercial</span>
            </div>

            @if(!$editing)
                <x-mary-button
                        icon="o-pencil"
                        label="Editar"
                        wire:click="enableEditing"
                        class="btn-sm"
                />
            @endif
        </div>
    </x-slot:title>

    <div class="space-y-4">
        @if($editing)
            {{-- Urgencia --}}
            <div>
                <x-mary-select
                        label="Nivel de Urgencia"
                        icon="o-bolt"
                        wire:model="urgency_level"
                        :options="[
                            ['id' => 'baja', 'name' => 'Baja'],
                            ['id' => 'media', 'name' => 'Media'],
                            ['id' => 'alta', 'name' => 'Alta'],
                            ['id' => 'inmediata', 'name' => 'Inmediata'],
                        ]"
                        class="input-primary"
                />
            </div>

            {{-- Presupuesto --}}
            <div>
                <x-mary-input
                        label="Presupuesto"
                        icon="o-banknotes"
                        type="number"
                        wire:model="budget"
                        min="0"
                        class="input-primary"
                />
            </div>

            {{-- Canal de Adquisición --}}
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
                        class="input-primary"
                />
            </div>

            {{-- Clasificación --}}
            <div>
                <x-mary-select
                        label="Clasificación"
                        icon="o-tag"
                        wire:model="classification"
                        :options="[
                            ['id' => 'activo', 'name' => 'Activo'],
                            ['id' => 'descartado', 'name' => 'Descartado'],
                            ['id' => 'reciclado', 'name' => 'Reciclado'],
                        ]"
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
                        class="input-primary"
                />
            </div>
            <div class="flex gap-2 justify-end">
                <x-mary-button
                        label="Cancelar"
                        wire:click="cancelEditing"
                        wire:loading.attr="disabled"
                        wire:target="cancelEditing"
                        class="btn-ghost"
                />

                <x-mary-button
                        label="Guardar"
                        wire:click="update"
                        wire:loading.attr="disabled"
                        wire:target="update"
                        class="btn-primary"
                />
            </div>
        @else
            {{-- Urgencia --}}
            <div>
                <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Nivel de Urgencia</label>
                @php
                    $urgencyColor = match($lead->urgency_level) {
                        'inmediata' => 'badge-error',
                        'alta' => 'badge-error',
                        'media' => 'badge-warning',
                        'baja' => 'badge-info',
                        default => 'badge-secondary'
                    };
                @endphp
                <x-mary-badge :value="ucfirst($lead->urgency_level ?? 'No disponible')" class="badge {{ $urgencyColor }}" />
            </div>

            {{-- Presupuesto --}}
            <div>
                <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Presupuesto</label>
                <div class="font-medium">$ {{ $lead->budget ?? 'No disponible' }}</div>
            </div>

            {{-- Canal de Adquisición --}}
            <div>
                <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Canal de Adquisición</label>
                <div class="font-medium">{{ $lead->undermine_channel ?? 'No disponible' }}</div>
            </div>

            {{-- Clasificación --}}
            <div>
                <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Clasificación</label>
                @php
                    $classificationColor = match($lead->classification) {
                        'activo' => 'badge-success',
                        'reciclado' => 'badge-warning',
                        'descartado' => 'badge-error',
                        default => 'badge-secondary'
                    };
                @endphp
                <x-mary-badge :value="ucfirst($lead->classification ?? 'No disponible')" class="badge {{ $classificationColor }}" />
            </div>

            {{-- Etapa del Embudo --}}
            <div>
                <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Embudo</label>
                @php
                    $funnelColor = match($lead->funnel_stage) {
                        'ventas' => 'badge-success',
                        'posventa' => 'badge-warning',
                        'fidelización' => 'badge-success',
                        'reactivación' => 'badge-error',
                        default => 'badge-secondary'
                    };
                @endphp
                <x-mary-badge :value="ucfirst($lead->funnel_stage ?? 'No disponible')" class="badge {{ $funnelColor }}" />
            </div>
        @endif


        {{-- Probabilidad --}}
        <div>
            <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Probabilidad (%)</label>
            <div class="flex items-center gap-2">
                <div class="font-medium {{ $lead->probability >= 80 ? 'text-success' : ($lead->probability >= 50 ? 'text-warning' : ($lead->probability >= 20 ? 'text-info' : 'text-error')) }}">
                    {{ $lead->probability ?? '0' }}%
                </div>
                <progress
                        class="progress flex-1 {{ $lead->probability >= 80 ? 'progress-success' : ($lead->probability >= 50 ? 'progress-warning' : ($lead->probability >= 20 ? 'progress-info' : 'progress-error')) }}"
                        value="{{ $lead->probability ?? 0 }}"
                        max="100"
                ></progress>
            </div>
        </div>
    </div>
</x-mary-card>

