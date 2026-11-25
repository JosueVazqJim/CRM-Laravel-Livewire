@props(['product', 'active'])

<x-mary-card class="shadow-lg">
    <h3 class="text-lg font-semibold mb-3 flex items-center gap-2">
        <x-mary-icon name="o-cog" class="w-5 h-5" />
        Acciones rápidas
    </h3>

    <div class="flex flex-col gap-3">
        <x-mary-button
            label="Gestionar Stock"
            icon="o-cube"
            :link="route('products.serials.index', $product)"
            class="btn-primary w-full"
        />

        @hasanyrole('admin')
        <x-mary-button
            label="Editar Producto"
            icon="o-pencil"
            :link="route('products.update', $product)"
            class="btn-outline w-full"
        />
        @endhasanyrole

        @hasanyrole('admin')
        <x-mary-button
                :label="$active ? 'Desactivar' : 'Activar'"
                :class="$active ? 'btn-error w-full justify-start' : 'btn-primary w-full justify-center'"
                wire:click="toggleActive"
                wire:loading.attr="disabled"
                wire:target="toggleActive"
                class="w-full justify-start"
        />
        @endhasanyrole

    </div>
</x-mary-card>

