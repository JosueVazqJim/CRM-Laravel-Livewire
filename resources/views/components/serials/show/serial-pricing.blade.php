@props(['serial'])

<x-mary-card class="shadow-lg">
    <x-slot:title class="flex items-center gap-2">
        <x-mary-icon name="o-currency-dollar" />
        Información de Precios
    </x-slot:title>

    <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-base-content/60 mb-1">Precio de Compra</p>
                @if($serial->purchase_price)
                    <div class="flex items-baseline gap-2">
                        <p class="text-2xl font-bold">{{ number_format($serial->purchase_price, 2) }}</p>
                        <span class="text-sm text-base-content/60">{{ $serial->product?->currency ?? 'USD' }}</span>
                    </div>
                @else
                    <p class="text-base-content/50 italic">No especificado</p>
                @endif
            </div>

            <div>
                <p class="text-sm text-base-content/60 mb-1">Precio de Venta (Referencia del Producto)</p>
                @if($serial->product?->price_regular)
                    <div class="flex items-baseline gap-2">
                        <p class="text-2xl font-bold">{{ number_format($serial->product->price_regular, 2) }}</p>
                        <span class="text-sm text-base-content/60">{{ $serial->product?->currency ?? 'USD' }}</span>
                    </div>
                @else
                    <p class="text-base-content/50 italic">No especificado</p>
                @endif
            </div>
        </div>

        @if($serial->purchase_price && $serial->product?->price_regular)
            <div class="divider my-2"></div>

            <div class="alert alert-info">
                <div class="flex justify-between items-center">
                    <span>Margen de Ganancia:</span>
                    @php
                        $margin = $serial->product->price_regular - $serial->purchase_price;
                        $percentage = ($margin / $serial->purchase_price) * 100;
                    @endphp
                    <span class="font-bold">
                        {{ number_format($margin, 2) }}
                        ({{ number_format($percentage, 1) }}%)
                    </span>
                </div>
            </div>
        @endif
    </div>
</x-mary-card>

