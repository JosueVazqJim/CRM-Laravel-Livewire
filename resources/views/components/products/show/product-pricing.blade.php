@props(['product'])

<x-mary-card class="shadow-lg">
    <div class="flex items-center gap-2 mb-4">
        <x-mary-icon name="o-currency-dollar" class="w-6 h-6 text-gray-800 dark:text-gray-400" />
        <h3 class="text-lg font-semibold">Precios de venta</h3>
    </div>
    <div class="space-y-2 text-sm">
        <div class="flex justify-between py-2 border-b"><span class="font-semibold">Regular:</span><span>{{ $product->currency }} {{ number_format($product->price_regular, 2) }}</span></div>
        @if($product->price_min)<div class="flex justify-between py-2 border-b"><span class="font-semibold">Mínimo:</span><span>{{ $product->currency }} {{ number_format($product->price_min, 2) }}</span></div>@endif
        @if($product->price_max)<div class="flex justify-between py-2 border-b"><span class="font-semibold">Máximo:</span><span>{{ $product->currency }} {{ number_format($product->price_max, 2) }}</span></div>@endif
        @if($product->installation_cost)<div class="flex justify-between py-2 border-b"><span class="font-semibold">Instalación:</span><span>{{ $product->currency }} {{ number_format($product->installation_cost, 2) }}</span></div>@endif
    </div>
</x-mary-card>

