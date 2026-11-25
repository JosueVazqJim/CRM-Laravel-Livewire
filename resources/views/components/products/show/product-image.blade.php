@props(['product'])

<x-mary-card class="shadow-lg bg-linear-to-br from-base-100 to-base-200">
    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-64 object-cover rounded-lg mb-4" />
    <h2 class="text-2xl font-bold mb-2">{{ $product->name }}</h2>
    <div class="flex justify-between py-2 border-b">
        <span class="font-semibold text-gray-800 dark:text-gray-400">SKU:</span>
        <span>{{ $product->sku ?? 'N/A' }}</span>
    </div>

    <div class="mt-4">
        @if($product->color)

            <div class="flex justify-between py-2 border-b">
                <span class="font-semibold text-gray-800 dark:text-gray-400">Color:
                {{ $product->color }}
                </span>
                <x-mary-badge
                        :value="$product->color"
                        class="badge"
                        style="background-color: {{ $product->color }}; color: {{ $product->color }};"
                />
            </div>
        @endif
    </div>

    <div class="flex justify-between py-2 border-b">
        <span class="font-semibold text-gray-800 dark:text-gray-400">Fecha de creación:</span>
        <span>{{ $product->created_at?->format('d/m/Y') ?? 'N/A' }}</span>
    </div>

    <div class="flex justify-between py-2 border-b">
        <span class="font-semibold text-gray-800 dark:text-gray-400">Fecha de actualización:</span>
        <span>{{ $product->updated_at?->format('d/m/Y') ?? 'N/A' }}</span>
    </div>
</x-mary-card>

