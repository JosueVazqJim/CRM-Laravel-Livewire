@props(['product'])

@php
    $headers = [
        ['key' => 'spec_key', 'label' => 'Clave'],
        ['key' => 'spec_value', 'label' => 'Valor'],
    ];
@endphp

<x-mary-card class="shadow-lg">
    <div class="space-y-4">
        <div class="flex items-center gap-2 mb-4">
            <x-mary-icon name="o-squares-plus" class="w-6 h-6 text-gray-800 dark:text-gray-400" />
            <h3 class="text-lg font-semibold">Especificaciones</h3>
        </div>

        @if($product->specifications->isNotEmpty())
            <x-mary-table
                    no-headers no-hover
                    :headers="$headers"
                    :rows="$product->specifications->map(fn($s) => [
                        'spec_key' => $s->spec_key,
                        'spec_value' => $s->spec_value,
                    ])"
            />
        @else
            <p class="text-sm text-gray-800 dark:text-gray-400 italic">Sin especificaciones agregadas</p>
        @endif
    </div>
</x-mary-card>

