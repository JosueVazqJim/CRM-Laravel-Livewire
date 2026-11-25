@props(['serial'])

@php
    $statusColors = [
        'in_stock' => 'badge-success',
        'sold' => 'badge-info',
        'damaged' => 'badge-error',
        'defective' => 'badge-error',
        'return' => 'badge-warning',
    ];

    $statusLabels = [
        'in_stock' => 'En Stock',
        'sold' => 'Vendido',
        'damaged' => 'Dañado',
        'defective' => 'Defectuoso',
        'return' => 'Retornado',
    ];

    $statusActions = [
        'in_stock' => [
            ['status' => 'sold', 'label' => 'Marcar como Vendido', 'icon' => 'o-shopping-cart'],
            ['status' => 'damaged', 'label' => 'Marcar como Dañado', 'icon' => 'o-exclamation-triangle'],
            ['status' => 'defective', 'label' => 'Marcar como Defectuoso', 'icon' => 'o-exclamation-circle'],
        ],
        'sold' => [
            ['status' => 'in_stock', 'label' => 'Retornar al Stock', 'icon' => 'o-exclamation-triangle'],
            ['status' => 'return', 'label' => 'Marcar como Retorno', 'icon' => 'o-arrow-uturn-left'],
        ],
        'damaged' => [
            ['status' => 'in_stock', 'label' => 'Retornar al Stock', 'icon' => 'o-exclamation-triangle'],
        ],
        'defective' => [
            ['status' => 'in_stock', 'label' => 'Retornar al Stock', 'icon' => 'o-exclamation-triangle'],
        ],
        'return' => [
            ['status' => 'in_stock', 'label' => 'Retornar al Stock', 'icon' => 'o-exclamation-triangle'],
        ],
    ];
@endphp

<x-mary-card class="shadow-lg">
    <x-slot:title class="flex items-center gap-2">
        <x-mary-icon name="o-clock" />
        Estado del Serial
    </x-slot:title>

    <div class="space-y-4">
        <div class="flex items-center gap-3">
            <x-mary-badge
                :value="$statusLabels[$serial->status] ?? ucfirst(str_replace('_', ' ', $serial->status))"
                :class="'badge ' . ($statusColors[$serial->status] ?? 'badge-base')"
            />
        </div>

        @if($statusActions[$serial->status] ?? null)
            <div class="divider my-2"></div>

            <p class="text-sm text-base-content/60 mb-2">Cambiar a:</p>

            <div class="flex flex-col gap-2">
                @foreach($statusActions[$serial->status] as $action)
                    <x-mary-button
                        label="{{ $action['label'] }}"
                        icon="{{ $action['icon'] }}"
                        wire:click="$wire.transitionTo('{{ $action['status'] }}')"
                        x-on:click="$wire.transitionTo('{{ $action['status'] }}')"
                        class="btn-outline btn-sm"
                    />
                @endforeach
            </div>
        @endif
    </div>
</x-mary-card>

