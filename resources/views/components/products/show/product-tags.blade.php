@props(['product'])

<x-mary-card class="shadow-lg">
    <div class="space-y-4">
        <div class="flex items-center gap-2 mb-4">
            <x-mary-icon name="o-tag" class="w-6 h-6 text-gray-800 dark:text-gray-400" />
            <h3 class="text-lg font-semibold">Etiquetas</h3>
        </div>

        @if($product->tags->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                @foreach($product->tags as $tag)
                    <x-mary-badge :value="$tag->name" class="badge-primary" />
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-800 dark:text-gray-400 italic">Sin etiquetas asignadas</p>
        @endif
    </div>
</x-mary-card>

