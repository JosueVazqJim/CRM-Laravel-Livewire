@props(['serial'])

<x-mary-card class="shadow-lg">
    <x-slot:title class="flex items-center gap-2">
        <x-mary-icon name="o-calendar" />
        Fechas Importantes
    </x-slot:title>

    <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-base-content/60 mb-1">Fecha de Compra</p>
                @if($serial->purchased_at)
                    <p class="font-semibold">{{ $serial->purchased_at->format('d/m/Y') }}</p>
                    <p class="text-xs text-base-content/50">{{ $serial->purchased_at->format('H:i') }}</p>
                @else
                    <p class="text-base-content/50 italic">No especificada</p>
                @endif
            </div>

            <div>
                <p class="text-sm text-base-content/60 mb-1">Entrada al Stock</p>
                @if($serial->entered_stock_at)
                    <p class="font-semibold">{{ $serial->entered_stock_at->format('d/m/Y') }}</p>
                    <p class="text-xs text-base-content/50">{{ $serial->entered_stock_at->format('H:i') }}</p>
                @else
                    <p class="text-base-content/50 italic">No especificada</p>
                @endif
            </div>

            <div>
                <p class="text-sm text-base-content/60 mb-1">Fecha de Venta</p>
                @if($serial->sold_at)
                    <p class="font-semibold">{{ $serial->sold_at->format('d/m/Y') }}</p>
                    <p class="text-xs text-base-content/50">{{ $serial->sold_at->format('H:i') }}</p>
                @else
                    <p class="text-base-content/50 italic">No vendido</p>
                @endif
            </div>

            <div>
                <p class="text-sm text-base-content/60 mb-1">Fecha de Salida</p>
                @if($serial->exited_at)
                    <p class="font-semibold">{{ $serial->exited_at->format('d/m/Y') }}</p>
                    <p class="text-xs text-base-content/50">{{ $serial->exited_at->format('H:i') }}</p>
                @else
                    <p class="text-base-content/50 italic">No salió</p>
                @endif
            </div>
        </div>
    </div>
</x-mary-card>

