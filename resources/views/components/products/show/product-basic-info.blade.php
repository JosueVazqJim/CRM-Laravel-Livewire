@props(['product'])

<x-mary-card class="shadow-lg">
    <div class="space-y-4">
        <div class="flex items-center gap-2 mb-4">
            <x-mary-icon name="o-information-circle" class="w-6 h-6 text-gray-800 dark:text-gray-400" />
            <h3 class="text-lg font-semibold">Información Básica</h3>
        </div>

        <div class="space-y-3 text-sm">
            <div class="flex justify-between py-2 border-b">
                <span class="font-semibold text-gray-800 dark:text-gray-400">Tipo:</span>
                <span>{{ $product->type->name ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b">
                <span class="font-semibold text-gray-800 dark:text-gray-400">Categoría:</span>
                <span>{{ $product->category->name ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b">
                <span class="font-semibold text-gray-800 dark:text-gray-400">Corporativo:</span>
                <span>{{ $product->company->name ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b">
                <span class="font-semibold text-gray-800 dark:text-gray-400">Moneda:</span>
                <span>{{ $product->currency }}</span>
            </div>
        </div>
    </div>
</x-mary-card>

