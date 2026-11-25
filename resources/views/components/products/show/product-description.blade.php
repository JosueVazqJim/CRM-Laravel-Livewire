@props(['product'])

<x-mary-card class="shadow-lg">
    <div class="flex items-center gap-2 mb-4">
        <x-mary-icon name="o-document-text" class="w-6 h-6 text-gray-800 dark:text-gray-400" />
        <h3 class="text-lg font-semibold">Descripción</h3>
    </div>
    @if($product->short_description)
        <div class="mb-4"><h4 class="font-semibold text-sm text-gray-800 dark:text-gray-400 mb-2">Descripción Breve</h4><p class="text-sm">{{ $product->short_description }}</p></div>
    @endif
    @if($product->long_description)
        <div><h4 class="font-semibold text-sm text-gray-800 dark:text-gray-400 mb-2">Descripción Detallada</h4><p class="text-sm whitespace-pre-wrap">{{ $product->long_description }}</p></div>
    @endif
    @if(!$product->short_description && !$product->long_description)
        <p class="text-sm text-gray-800 dark:text-gray-400 italic">Sin descripción disponible</p>
    @endif
</x-mary-card>

