@props(['serial'])

@php
    $statusLabels = [
        'in_stock' => 'En Stock',
        'sold' => 'Vendido',
        'damaged' => 'Dañado',
        'defective' => 'Defectuoso',
        'return' => 'Retornado',
    ];

    $statusColors = [
        'in_stock' => 'bg-success/20 text-success',
        'sold' => 'bg-info/20 text-info',
        'damaged' => 'bg-error/20 text-error',
        'defective' => 'bg-error/20 text-error',
        'return' => 'bg-warning/20 text-warning',
    ];
@endphp

<x-mary-card class="shadow-lg">
    <x-slot:title class="flex items-center gap-2">
        <x-mary-icon name="o-arrow-path" />
        Historial de Movimientos
    </x-slot:title>

    <div class="space-y-0">
        @forelse($serial->movements as $movement)
            <div class="flex gap-4 py-3 border-b last:border-b-0">
                <div>
                    <div class="w-12 h-12 rounded-full {{ $statusColors[$movement->to_status] ?? 'bg-base-300' }} flex items-center justify-center flex-shrink-0">
                        <x-mary-icon name="o-arrow-path" class="w-5 h-5" />
                    </div>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-1">
                        <p class="font-semibold text-sm">
                            Cambio de estado:
                            <span class="text-base-content/60">
                                @if($movement->from_status)
                                    {{ $statusLabels[$movement->from_status] ?? $movement->from_status }}
                                @else
                                    Creado
                                @endif
                            </span>
                            →
                            <span class="font-bold text-base-content">{{ $statusLabels[$movement->to_status] ?? $movement->to_status }}</span>
                        </p>
                        <span class="text-xs text-base-content/50 whitespace-nowrap">
                            {{ $movement->moved_at->format('d/m/Y H:i') }}
                        </span>
                    </div>

                    <p class="text-xs text-base-content/60 mb-2">
                        Por: {{ $movement->user?->name ?? 'Sistema' }} ({{ $movement->user?->email ?? '' }})
                    </p>

                    @if($movement->notes)
                        <div class="bg-base-200/50 rounded p-2 text-xs">
                            <p class="whitespace-pre-wrap">{{ $movement->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-6 text-base-content/50">
                <x-mary-icon name="o-information-circle" label="Sin movimientos registrados." />
            </div>
        @endforelse
    </div>
</x-mary-card>

