<section class="w-full">
    <div class="relative mb-6 w-full">
        <x-mary-header title="Usuarios" subtitle="Listado y configuración de usuarios." separator progress-indicator>
            <x-slot:middle class="!justify-end">
                <x-mary-input
                        placeholder="Buscar por nombre, apellido o email..."
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

                @hasanyrole('admin')
                <x-mary-button label="Crear Usuario" icon="o-plus" :link="route('users.create')" class="btn-primary" responsive />
                @endhasanyrole
            </x-slot:actions>
        </x-mary-header>
    </div>

    {{--  TABLE --}}
    <x-mary-card>
        <x-mary-table :headers="$headers" :rows="$users" :sort-by="$sortBy" with-pagination striped
                            :link="url('users') . '/{slug}'" >
            {{--            --}}{{-- Avatar scope --}}
            @scope('cell_avatar', $user)
            <x-mary-avatar :image="$user->avatar_url " class="!w-10" />
            @endscope

            @scope('cell_active', $user)
            <x-mary-badge :value="$user->active ? 'Activo' : 'Inactivo'" class="{{ $user->active ? 'badge bg-primary/40' : 'badge bg-error/40' }}" />
            @endscope
            <x-slot:empty>
                <x-mary-icon name="o-user-group" label="No se encontraron usuarios." />
            </x-slot:empty>


        </x-mary-table>
    </x-mary-card>

{{--     FILTERS --}}
    <x-mary-drawer wire:model="showFilters" title="Filtros" class="lg:w-1/3" right separator with-close-button>
        <div class="grid gap-5">
            <x-mary-datetime label="Fecha de creación" wire:model.live.debounce="created_at" class="input-primary"/>
            <x-mary-datetime label="Fecha de actualización" wire:model.live.debounce="updated_at" class="input-primary"/>

            <x-mary-choices-offline
                    label="Selecciona las empresas que administra"
                    wire:model.live.debounce="companies"
                    :options="$companyOptions"
                    placeholder="Buscar empresa..."
                    no-result-text="No se encontraron empresas"
                    clearable
                    searchable
                    class="w-full select-primary "
            />
        </div>

{{--         ACTIONS --}}
        <x-slot:actions>
            <x-mary-button label="Quitar filtros" icon="o-x-mark" wire:click="clear" spinner />
            <x-mary-button label="Aplicar" icon="o-check" class="btn-primary" @click="$wire.showFilters = false" />
        </x-slot:actions>
    </x-mary-drawer>

</section>


