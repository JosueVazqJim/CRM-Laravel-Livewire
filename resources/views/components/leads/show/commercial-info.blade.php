@props(['lead'])

<x-mary-card class="shadow-xl">
    <x-slot:title>
        <div class="flex items-center gap-2">
            <x-mary-icon name="o-briefcase" class="h-5 w-5"></x-mary-icon>
            <span>Información Comercial</span>
        </div>
    </x-slot:title>

    <div class="space-y-4">
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

