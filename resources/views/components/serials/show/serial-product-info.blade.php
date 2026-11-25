@props(['serial'])

<x-mary-card class="shadow-lg">
    <x-slot:title class="flex items-center gap-2">
        <x-mary-icon name="o-cube" />
        Producto Asociado
    </x-slot:title>

    <div class="space-y-4">
        <div class="flex gap-4 items-start">
            @if($serial->product?->image_url)
                <img
                    src="{{ $serial->product->image_url }}"
                    alt="{{ $serial->product->name }}"
                    class="w-24 h-24 object-cover rounded border"
                />
            @else
                <div class="w-24 h-24 bg-base-300 rounded border flex items-center justify-center">
                    <x-mary-icon name="o-photo" class="text-base-content/40" />
                </div>
            @endif

            <div class="flex-1">
                <h3 class="text-lg font-semibold mb-2">{{ $serial->product?->name }}</h3>

                <div class="space-y-1 text-sm">
                    <div>
                        <p class="text-base-content/60">SKU</p>
                        <p class="font-mono font-semibold">{{ $serial->product?->sku }}</p>
                    </div>
                    <div>
                        <p class="text-base-content/60">Categoría</p>
                        <p class="font-semibold">{{ $serial->product?->category?->name ?? 'Sin categoría' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="divider my-2"></div>

        <div class="flex gap-2">
            <x-mary-button
                label="Ver Producto"
                icon="o-arrow-top-right-on-square"
                :link="route('products.show', $serial->product)"
                class="btn-outline btn-sm flex-1"
            />
        </div>
    </div>
</x-mary-card>

