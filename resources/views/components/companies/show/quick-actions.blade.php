@props(['company', 'active'])

<x-mary-card class="shadow-xl">
    <h3 class="text-lg font-semibold mb-3 flex items-center gap-2">
        <x-mary-icon name="o-cog" class="w-5 h-5" />
        Acciones rápidas
    </h3>

    <div class="flex flex-col gap-3">
        <x-mary-button
                label="Editar información"
                icon="o-pencil"
                :link="route('companies.update', $company)"
                class="btn-outline w-full"
        />
        <x-mary-button
                :label="$active ? 'Desactivar' : 'Activar'"
                :class="$active ? 'btn-error w-full justify-start' : 'btn-primary w-full justify-center'"
                wire:click="toggleActive"
                wire:loading.attr="disabled"
                wire:target="toggleActive"
                class="w-full justify-start"
        />
    </div>
</x-mary-card>