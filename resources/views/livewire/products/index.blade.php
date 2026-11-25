<section class="w-full">
    <div class="relative mb-6 w-full">
        <x-mary-header title="Productos" subtitle="Gestión del catálogo de productos." separator progress-indicator>
            <x-slot:middle class="!justify-end">
                <x-mary-input
                    placeholder="Buscar por nombre, SKU..."
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
                <x-mary-button label="Crear Producto" icon="o-plus" :link="route('products.create')" class="btn-primary" responsive />
                @endhasanyrole
            </x-slot:actions>
        </x-mary-header>
    </div>

    {{-- SELECTOR DE EMPRESA --}}
    <x-mary-card class="mb-2">
        <div class="flex flex-col sm:flex-row items-center gap-4">
            @php
                $selectedCompany = collect($companyOptions)->firstWhere('id', $company);
            @endphp

            @if($selectedCompany && $company > 0)
                <div class="flex-shrink-0">
                    <x-mary-avatar
                            :image="$selectedCompany['avatar'] ?? asset('images/empty-company.jpg')"
                            class="!w-36 !h-16 !rounded-lg object-cover shadow-sm"
                    />
                </div>
            @else
                <div class="flex-shrink-0 opacity-50">
                    <x-mary-avatar
                            image="{{ asset('images/empty-company.jpg') }}"
                            class="!w-24 !h-16 !rounded-lg object-cover grayscale shadow-sm"
                    />
                </div>
            @endif

            <div class="flex-1 w-full">
                <x-mary-choices-offline
                        label="Selecciona la empresa del corporativo donde deseas consultar"
                        wire:model.live.debounce="company"
                        :options="$companyOptions"
                        placeholder="Buscar empresa..."
                        no-result-text="No se encontraron empresas"
                        single
                        searchable
                        class="w-full select-primary"
                />
            </div>
        </div>
    </x-mary-card>

    @if(empty($companyOptions))
        <x-mary-alert title="No tienes empresas asignadas. Contacta al administrador." icon="o-exclamation-triangle" class="alert-warning mb-4" />
    @else


    {{--  TABLE --}}
    <x-mary-card>
        <x-mary-table :headers="$headers" :rows="$products" :sort-by="$sortBy" with-pagination striped
            :per-page-values="\App\Livewire\Products\Index::PER_PAGE_OPTIONS"
            per-page="perPage"
            :link="url('products') . '/{slug}'" >

            @scope('cell_image_path', $products)
            <x-mary-avatar
                    :image="$products->image_url"
            />
            @endscope

            @scope('cell_stock_count', $products)
            <div class="flex items-center gap-2">
                <x-mary-badge
                    :value="$products->stock_count"
                    :class="$products->stock_count == 0 ? 'badge bg-error/40' : ($products->stock_count <= 5 ? 'badge bg-warning/40' : 'badge bg-success/40')"
                />
            </div>
            @endscope

            @scope('cell_active', $products)
            <x-mary-badge :value="$products->active ? 'Activo' : 'Inactivo'" class="{{ $products->active ? 'badge bg-primary/40' : 'badge bg-error/40' }}" />
            @endscope

            <x-slot:empty>
                <x-mary-icon name="o-cube" label="No se encontraron productos." />
            </x-slot:empty>
        </x-mary-table>
    </x-mary-card>

    {{--     FILTERS --}}
    <x-mary-drawer wire:model="showFilters" title="Filtros" class="lg:w-1/3" right separator with-close-button>
        <div class="grid gap-5">
            <x-mary-input label="SKU" wire:model.live.debounce="sku" class="input-primary"/>

            <x-mary-select label="Categoría" wire:model.live="category_id" :options="$categoriesOptions" placeholder="Seleccionar..." class="select-primary" />

            <x-mary-select label="Tipo" wire:model.live="type_id" :options="$typesOptions" placeholder="Seleccionar..." class="select-primary" />

            <x-mary-choices-offline
                label="Etiquetas"
                wire:model.live="tags"
                :options="$tagsOptions"
                placeholder="Buscar etiquetas..."
                no-result-text="No se encontraron etiquetas"
                searchable
                clearable
                class="select-primary"
            />

            <x-mary-select label="Moneda" wire:model.live="currency" :options="$currencyOptions" placeholder="Seleccionar..." class="select-primary" />

            <x-mary-datetime label="Fecha de creación" wire:model.live.debounce="created_at" class="input-primary"/>
            <x-mary-datetime label="Fecha de actualización" wire:model.live.debounce="updated_at" class="input-primary"/>
        </div>

        {{--         ACTIONS --}}
        <x-slot:actions>
            <x-mary-button label="Quitar filtros" icon="o-x-mark" wire:click="clear" spinner />
            <x-mary-button label="Aplicar" icon="o-check" class="btn-primary" @click="$wire.showFilters = false" />
        </x-slot:actions>
    </x-mary-drawer>

    @endif

</section>
