@props(['product'])

<x-mary-card class="shadow-lg">
    <div class="space-y-4">
        <div class="flex items-center gap-2 mb-4">
            <x-mary-icon name="o-cube-transparent" class="w-6 h-6 text-gray-800 dark:text-gray-400" />
            <h3 class="text-lg font-semibold">Estado del Stock</h3>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                <span class="font-semibold">Stock Total:</span>
                <x-mary-badge
                        :value="$product->serialsTotal()->count()"
                        class="badge-lg bg-primary/30"
                />
            </div>

            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                <span class="font-semibold">En Stock:</span>
                <x-mary-badge :value="$product->serialsInStock()->count()"
                              :class="$product->serialsInStock()->count() ? 'badge-lg bg-error/40' : ($product->serialsInStock()->count() <= 5 ? 'badge-lg bg-warning/40' : 'badge-lg bg-success/40')"
                />

            </div>

            @if($product->stock_count == 0)
                <div class="alert alert-error">
                    <x-mary-icon name="o-exclamation-triangle" class="w-5 h-5" />
                    <span>Producto agotado</span>
                </div>
            @elseif($product->isLowStock())
                <div class="alert alert-warning">
                    <x-mary-icon name="o-exclamation-circle" class="w-5 h-5" />
                    <span>Stock bajo</span>
                </div>
            @endif
        </div>
    </div>
</x-mary-card>

