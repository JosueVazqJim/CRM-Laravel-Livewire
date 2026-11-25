<section class="w-full">
    <div class="relative mb-6 w-full">
        <x-mary-header title="Gestión de Series" subtitle="Administración de números de serie y stock." separator progress-indicator>
            <x-slot:middle class="!justify-end">
                <div class="flex flex-wrap items-center justify-end gap-3">
                    @if($product)
                        <a href="{{ route('products.show', $product) }}" wire:navigate class="inline-flex rounded-box bg-base-200 px-3 py-2 transition hover:bg-base-300">
                            <x-mary-avatar
                                :image="$product->image_url"
                                :title="$product->name"
                                :subtitle="'SKU: ' . ($product->sku ?: '—')"
                                class="!w-10 !rounded-lg"
                            />
                        </a>
                    @endif

                    <x-mary-input
                        placeholder="Buscar por número de serie..."
                        wire:model.live.debounce.500ms="search"
                        icon="o-magnifying-glass"
                        class="input-primary"
                        clearable
                    />
                </div>
            </x-slot:middle>

            <x-slot:actions>
                <x-mary-button label="Filtros"
                    icon="o-funnel"
                    :badge="$filterCount"
                    badge-classes="font-mono"
                    @click="$wire.showFilters = true"
                    class="bg-base-300"
                    responsive />

                @can('seriales.editar')
                <x-mary-button
                    icon="o-arrows-right-left"
                    wire:click="openBulkStatusModal"
                    x-bind:disabled="$wire.selectedSerials.length === 0"
                    class="btn-primary"
                    responsive
                >
                    <span class="hidden lg:block" x-text="$wire.selectedSerials.length ? `Cambiar estado (${$wire.selectedSerials.length})` : 'Cambiar estado'"></span>
                </x-mary-button>
                @endcan

                @can('seriales.crear')
                <x-mary-button label="Agregar Serial" icon="o-plus" :link="route('products.serials.create', $product)" class="btn-primary" responsive />
                @endcan
            </x-slot:actions>
        </x-mary-header>
    </div>

    {{--  TABLE --}}
    <x-mary-card>
        <x-mary-table
            :headers="$headers"
            :rows="$serials"
            :sort-by="$sortBy"
            :per-page-values="\App\Livewire\Products\Serials\Index::PER_PAGE_OPTIONS"
            per-page="perPage"
            with-pagination
            striped
            selectable
            wire:model="selectedSerials"
            :link="url('products/serials') . '/{id}'"
        >

            @scope('cell_status', $serials)
            <x-mary-badge
                :value="ucfirst(str_replace('_', ' ', $serials->status))"
                :class="$serials->status === 'in_stock' ? 'badge bg-success/40' : ($serials->status === 'sold' ? 'badge bg-info/40' : 'badge bg-error/40')"
            />
            @endscope

            @scope('cell_quote_folio', $serials)
            @if($serials->quoteVersion)
                <span class="font-mono font-semibold">{{ $serials->quoteVersion->folio }}</span>
            @else
                <span class="text-gray-400 text-sm">—</span>
            @endif
            @endscope

{{--            @scope('cell_flag_color', $serials)--}}
{{--            @if($serials->flag_color)--}}
{{--                <div class="flex items-center gap-2">--}}
{{--                    <div class="w-6 h-6 rounded border-2" style="background-color: {{ $serials->flag_color }}; border-color: {{ $serials->flag_color }};"></div>--}}
{{--                    <span class="text-xs">{{ $serials->flag_color }}</span>--}}
{{--                </div>--}}
{{--            @else--}}
{{--                <span class="text-gray-400 text-sm">Sin bandera</span>--}}
{{--            @endif--}}
{{--            @endscope--}}

            <x-slot:empty>
                <x-mary-icon name="o-cube" label="No se encontraron series de productos." />
            </x-slot:empty>
        </x-mary-table>
    </x-mary-card>

    {{--     FILTERS --}}
    <x-mary-drawer wire:model="showFilters" title="Filtros" class="lg:w-1/3" right separator with-close-button>
        <div class="grid gap-5">
            <x-mary-select
                label="Estado"
                wire:model.live="status_filter"
                :options="$statusOptions"
                option-key="id"
                option-value="id"
                option-label="name"
                placeholder="Todos los estados..."
                searcheable
                class="select-primary"
            />

{{--            <x-mary-choices label="Creado por" wire:model.live="user_id" :options="$userOptions" single clearable />--}}

            <x-mary-datetime label="Fecha de creación" wire:model.live.debounce="created_at" class="input-primary"/>
            <x-mary-datetime label="Fecha de actualización" wire:model.live.debounce="updated_at" class="input-primary"/>
            <x-mary-datetime label="Fecha de su compra" wire:model.live.debounce="purchased_at" class="input-primary"/>
            <x-mary-datetime label="Fecha de su ingreso" wire:model.live.debounce="entered_at" class="input-primary"/>
            <x-mary-datetime label="Fecha de venta" wire:model.live.debounce="sold_at" class="input-primary"/>
            <x-mary-datetime label="Fecha de salida" wire:model.live.debounce="exited_at" class="input-primary"/>


{{--            <x-mary-input label="Color de Bandera" wire:model.live.debounce="flag_color_filter" class="input-primary"/>--}}
{{--            <x-mary-colorpicker wire:model.live.debounce="flag_color_filter" label="Color de Bandera" suffix="Hex code" class="input-primary" />--}}
        </div>

        {{--         ACTIONS --}}
        <x-slot:actions>
            <x-mary-button label="Quitar filtros" icon="o-x-mark" wire:click="clear" spinner />
            <x-mary-button label="Aplicar" icon="o-check" class="btn-primary" @click="$wire.showFilters = false" />
        </x-slot:actions>
    </x-mary-drawer>

    {{--     CAMBIAR ESTADO (masivo) --}}
    <x-mary-modal
        wire:model="showBulkStatusModal"
        title="Cambiar estado"
        subtitle="Se aplicará a {{ count($selectedSerials) }} serial(es) seleccionado(s)."
        box-class="w-11/12 max-w-2xl"
    >
        <div class="grid grid-cols-1 gap-4">
            <x-mary-select
                label="Nuevo estado"
                wire:model.live="bulk_status"
                :options="$bulkStatusOptions"
                placeholder="Selecciona un estado..."
                class="select-primary"
            />

            @if($bulk_status === \App\Livewire\Products\Serials\Index::BULK_DELETE)
                <x-mary-alert
                    title="Esta acción no se puede deshacer"
                    description="Los seriales seleccionados y su historial de movimientos se eliminarán permanentemente de la base de datos."
                    icon="o-exclamation-triangle"
                    class="alert-error"
                />
            @endif
        </div>

        @if($bulk_status === \App\Models\Products\ProductSerial::STATUS_SOLD)
        <div class="mt-4 grid grid-cols-1 gap-4">
            <x-mary-choices-offline
                label="Vendido por"
                wire:model="bulk_sold_by_user_id"
                :options="$sellerOptions"
                no-result-text="No se encontraron usuarios"
                single
                searchable
                class="select-primary"
            />

            <x-mary-choices-offline
                label="Cotización (folio)"
                wire:model="bulk_quote_version_id"
                :options="$quoteVersionOptions"
                no-result-text="No se encontraron cotizaciones"
                single
                searchable
                clearable
                class="select-primary"
            />

            <x-mary-datetime
                type="datetime-local"
                label="Fecha y hora de venta"
                wire:model="bulk_sold_at"
                icon="o-calendar"
                class="input-primary"
            />

            <x-mary-datetime
                label="Fecha de salida"
                wire:model="bulk_exited_at"
                icon="o-calendar"
                class="input-primary"
            />
        </div>
        @endif

        <x-slot:actions>
            <x-mary-button label="Cancelar" wire:click="$set('showBulkStatusModal', false)" class="btn-ghost" />
            <x-mary-button
                :label="$bulk_status === \App\Livewire\Products\Serials\Index::BULK_DELETE ? 'Eliminar' : 'Confirmar'"
                wire:click="confirmBulkStatus"
                :class="$bulk_status === \App\Livewire\Products\Serials\Index::BULK_DELETE ? 'btn-error' : 'btn-primary'"
                spinner="confirmBulkStatus"
            />
        </x-slot:actions>
    </x-mary-modal>

</section>

