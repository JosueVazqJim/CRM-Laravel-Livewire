@props(['lead'])

<x-mary-card class="shadow-xl">
    <x-slot:title>
        <div class="flex items-center gap-2">
            <x-mary-icon name="o-user-group" class="h-5 w-5"></x-mary-icon>
            <span>Estado del Cliente</span>
        </div>
    </x-slot:title>

    <div class="space-y-4">
        {{-- Fase de Ventas --}}
        <div>
            <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Fase de Ventas</label>
            @php
                $progressColor = match($lead->client_progress) {
                    'sin contactar' => 'badge-warning',
                    'nuevo' => 'badge-info',
                    'contactado' => 'badge-primary',
                    'diagnosticado' => 'badge-secondary',
                    'negociación' => 'badge-warning',
                    'cierre(pendiente adelanto)' => 'badge-info',
                    'vendido' => 'badge-success',
                    'perdido' => 'badge-error',
                    default => 'badge-secondary'
                };
            @endphp
            <x-mary-badge :value="ucfirst($lead->client_progress ?? 'No disponible')" class="badge {{ $progressColor }}" />
        </div>

        {{-- Estado del Cliente --}}
        <div>
            <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Estado del Cliente</label>
            <div class="font-medium">{{ $lead->client_status ?? 'No disponible' }}</div>
        </div>

        {{-- Estado de Pago --}}
        <div>
            <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Estado de Pago</label>
            <div class="font-medium">{{ $lead->pay_status ?? 'No disponible' }}</div>
        </div>

        {{-- Post-Venta --}}
        <div>
            <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Post-Venta</label>
            <div class="font-medium">{{ $lead->after_sales ?? 'No disponible' }}</div>
        </div>
    </div>
</x-mary-card>

