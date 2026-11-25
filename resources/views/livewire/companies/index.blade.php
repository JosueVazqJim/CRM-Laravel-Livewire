<section class="w-full">
    <div class="relative mb-6 w-full">
        <x-mary-header title="Empresas del Corporativo RAMA" subtitle="Listado y configuración de empresas." separator progress-indicator>
            <x-slot:middle class="!justify-end">
                <x-mary-input
                        placeholder="Buscar por nombre o email..."
                        wire:model.live.debounce.500ms="search"
                        icon="o-magnifying-glass"
                        class="input-primary"
                        clearable
                />
            </x-slot:middle>

            <x-slot:actions>
                <x-mary-button label="Filtros"
                               icon="o-funnel"
                               :badge="$filterCount"
                               badge-classes="font-mono"
                               @click="$wire.showFilters = true"
                               class="bg-base-300"
                               responsive />

                {{--        @hasanyrole('Super Admin|Admin')--}}
                <x-mary-button label="Crear Empresa" icon="o-plus" :link="route('companies.create')" class="btn-primary" responsive />
                {{--        @endhasanyrole--}}
            </x-slot:actions>
        </x-mary-header>
    </div>

    {{--  TABLE --}}
    <x-mary-card>
        <x-mary-table :headers="$headers" :rows="$companies" :sort-by="$sortBy" with-pagination striped
                      :link="url('companies') . '/{slug}'" >
            {{--            --}}{{-- Avatar scope --}}
            @scope('cell_avatar', $companies)
            <x-mary-avatar :image="$companies->avatar_url " class="!w-14 !rounded-lg" />
            @endscope

            @scope('cell_active', $companies)
            <x-mary-badge :value="$companies->active ? 'Activo' : 'Inactivo'" class="{{ $companies->active ? 'badge bg-primary/40' : 'badge bg-error/40' }}" />
            @endscope
            <x-slot:empty>
                <x-mary-icon name="o-building-office-2" label="No se encontraron empresas." />
            </x-slot:empty>


        </x-mary-table>
    </x-mary-card>

    {{--     FILTERS --}}
    <x-mary-drawer wire:model="showFilters" title="Filtros" class="lg:w-1/3" right separator with-close-button>
        <div class="grid gap-5">
            <x-mary-datetime label="Fecha de creación" wire:model.live.debounce="created_at" class="input-primary"/>
            <x-mary-datetime label="Fecha de actualización" wire:model.live.debounce="updated_at" class="input-primary"/>

        </div>

        {{--         ACTIONS --}}
        <x-slot:actions>
            <x-mary-button label="Quitar filtros" icon="o-x-mark" wire:click="clear" spinner />
            <x-mary-button label="Aplicar" icon="o-check" class="btn-primary" @click="$wire.showFilters = false" />
        </x-slot:actions>
    </x-mary-drawer>

</section>


