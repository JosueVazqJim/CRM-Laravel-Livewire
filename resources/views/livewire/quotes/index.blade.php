<section class="w-full">
    <div class="relative mb-6 w-full">
        <x-mary-header
            title="Cotizaciones"
            :subtitle="auth()->user()->hasRole('admin') ? 'Cotizaciones por empresa y vendedor' : 'Mis cotizaciones'"
            separator
            progress-indicator
        >
            <x-slot:middle class="!justify-end">
                <x-mary-input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por folio o cliente..."
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
            </x-slot:actions>
        </x-mary-header>
    </div>

    <x-mary-card class="mb-2">
        <div class="flex flex-col sm:flex-row items-center gap-4">
            {{-- Logo de la empresa (optimizado para imágenes horizontales) --}}
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

            {{-- Selector --}}
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

    <x-mary-card>
        @if($company > 0)
            <x-mary-table
                :headers="$headers"
                :rows="$quotesVersions"
                :link="url('quotes') . '/{id}'"
                :per-page-values="\App\Livewire\Quotes\Index::PER_PAGE_OPTIONS"
                per-page="perPage"
                with-pagination
                striped
            >

                @scope('cell_folio', $quoteVersion)
                    <span class="font-mono font-semibold">{{ $quoteVersion->folio }}</span>
                @endscope

                @scope('cell_lead', $quoteVersion)
                    <div>
                        <div class="font-semibold text-sm">{{ $quoteVersion->quote->lead->name_company }}</div>
                        <div class="text-xs text-gray-400">{{ $quoteVersion->quote->company->name }}</div>
                    </div>
                @endscope

                @scope('cell_user', $quoteVersion)
                    <span class="text-sm">{{ $quoteVersion->quote->user->name }}</span>
                @endscope

                @scope('cell_status', $quoteVersion)
                    @php
                        $statusClass = match($quoteVersion->status) {
                            'en_proceso'    => 'badge-info',
                            'en_validacion' => 'badge-warning',
                            'exitosa'       => 'badge-success',
                            'sin_exito'     => 'badge-error',
                            'cancelada'     => 'badge-neutral',
                            'papelera'      => 'badge-ghost',
                            default         => 'badge-ghost',
                        };
                        $statusLabel = match($quoteVersion->status) {
                            'en_proceso'    => 'En Proceso',
                            'en_validacion' => 'En Validación',
                            'exitosa'       => 'Exitosa',
                            'sin_exito'     => 'Sin Éxito',
                            'cancelada'     => 'Cancelada',
                            'papelera'      => 'Papelera',
                            default         => $quoteVersion->status,
                        };
                    @endphp
                    <x-mary-badge :value="$statusLabel" class="badge {{ $statusClass }}" />
                @endscope

                @scope('cell_total', $quoteVersion)
                    <span class="font-mono">$ {{ number_format((float) $quoteVersion->total, 2) }} USD</span>
                @endscope

                @scope('cell_created_at', $quoteVersion)
                    {{ $quoteVersion->created_at->format('d/m/Y H:i') }}
                @endscope

                <x-slot:empty>
                    <x-mary-icon name="o-document-text" label="No se encontraron cotizaciones." />
                </x-slot:empty>

            </x-mary-table>
        @else
            <div class="text-center py-12">
                <x-mary-icon name="o-building-office-2" label="Selecciona una empresa para ver las cotizaciones" />
            </div>
        @endif
    </x-mary-card>

    <x-mary-drawer wire:model="showFilters" title="Filtros" class="lg:w-1/3" right separator with-close-button>
        <div class="grid gap-5">
            @if(auth()->user()->hasRole('admin'))
                <x-mary-select
                        label="Vendedor"
                        wire:model.live="userId"
                        :options="$userOptions"
                        option-value="id"
                        option-label="name"
                        placeholder="Todos los vendedores"
                        hint="Cotizaciones creadas por el vendedor (activos e inactivos)"
                        class="select-primary"
                        searchable
                        clearable
                />
            @endif

            <x-mary-select
                    label="Estado"
                    wire:model.live="status"
                    :options="$statusOptions"
                    placeholder="Todos los estados"
                    class="select-primary"
                    clearable
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <x-mary-datetime
                        label="Desde"
                        wire:model.live="dateFrom"
                        type="date"
                        class="input-primary"
                />
                <x-mary-datetime
                        label="Hasta"
                        wire:model.live="dateTo"
                        type="date"
                        class="input-primary"
                />
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Quitar filtros" icon="o-x-mark" wire:click="clear" spinner />
            <x-mary-button label="Aplicar" icon="o-check" class="btn-primary" @click="$wire.showFilters = false" />
        </x-slot:actions>
    </x-mary-drawer>
</section>
