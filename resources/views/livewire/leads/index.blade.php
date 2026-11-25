<section class="w-full">
    <div class="relative mb-6 w-full">
        <x-mary-header title="Leads" subtitle="Gestión y seguimiento de leads por empresa." separator progress-indicator>
            <x-slot:middle class="!justify-end">
                <x-mary-input
                        placeholder="Buscar por empresa, país, clasificación..."
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

                @if(auth()->user()->hasRole('admin'))
                    <x-mary-button
                        wire:click="toggleMyLeads"
                        icon="o-user"
                        label="{{ $showMyLeads ? 'Ver todos' : 'Mis Leads' }}"
                        class="{{ $showMyLeads ? 'btn-primary' : 'bg-base-300' }}"
                        responsive
                    />
                    <x-mary-button
                        wire:click="toggleSharedLeads"
                        icon="o-users"
                        label="{{ $showSharedLeads ? 'Ver todos' : 'Compartidos conmigo' }}"
                        class="{{ $showSharedLeads ? 'btn-primary' : 'bg-base-300' }}"
                        responsive
                    />
                    <x-mary-button
                        wire:click="toggleMetaLeads"
                        :icon="$showMetaLeads ? 'o-x-mark' : 's-megaphone'"
                        :label="$showMetaLeads ? 'Ver todos' : 'Leads Meta'"
                        :class="$showMetaLeads ? 'btn-primary' : 'bg-blue-300'"
                        responsive
                    />
                @endif

                 <x-mary-button label="Crear Lead" icon="o-plus" :link="route('leads.create')" class="btn-primary" responsive />
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

    @if($showMetaLeads)
        <div class="alert alert-accent mb-2">
            <x-mary-icon name="s-megaphone" class="w-5 h-5"/>
            <span>
                Mostrando leads de Meta sin asignar.
                Haz clic en un lead para verlo y asignarlo.
            </span>
            <x-mary-button
                label="Ver todos los leads"
                wire:click="toggleMetaLeads"
                class="btn-ghost btn-sm ml-auto"
            />
        </div>
    @endif

    @if($showMyLeads)
        <div class="alert alert-primary mb-2">
            <x-mary-icon name="o-user" class="w-5 h-5"/>
            <span>
                Mostrando solo tus leads en
                {{ collect($companyOptions)->firstWhere('id', $company)['name'] ?? 'la empresa seleccionada' }}.
            </span>
            <x-mary-button
                label="Ver todos"
                wire:click="toggleMyLeads"
                class="btn-ghost btn-sm ml-auto"
            />
        </div>
    @endif

    @if($showSharedLeads)
        <div class="alert alert-primary mb-2">
            <x-mary-icon name="o-users" class="w-5 h-5"/>
            <span>
                Mostrando los leads que han sido compartidos contigo en
                {{ collect($companyOptions)->firstWhere('id', $company)['name'] ?? 'la empresa seleccionada' }}.
            </span>
            <x-mary-button
                label="Ver todos"
                wire:click="toggleSharedLeads"
                class="btn-ghost btn-sm ml-auto"
            />
        </div>
    @endif

    <x-mary-card>
        @if($company > 0)
            <x-mary-table
                    :headers="$headers"
                    :rows="$leads"
                    :sort-by="$sortBy"
                    :per-page-values="\App\Livewire\Leads\Index::PER_PAGE_OPTIONS"
                    per-page="perPage"
                    with-pagination
                    striped
                    :link="url('leads') . '/{id}'"
            >
                @scope('cell_state', $lead)
                    {{ $lead->state->name ?? '—' }}
                @endscope

                @scope('cell_contact', $lead)
                    {{ $lead->contactPeople->first()->name_contact ?? '—' }}
                @endscope

                @scope('cell_user', $lead)
                <div class="flex items-center gap-2">
                    @php $displayUser = $lead->display_user; @endphp

                    @if($displayUser)
                        <span class="text-sm">
                {{ $displayUser->first_name }}
                            {{ $displayUser->paternal_surname }}
                            {{ $displayUser->maternal_surname }}
            </span>
                    @else
                        <span class="text-sm italic text-gray-500">Sin asignar</span>
                    @endif
                </div>
                @endscope

                @scope('cell_urgency_level', $lead)
                @php
                    $urgencyColor = match($lead->urgency_level) {
                        'inmediata' => 'badge-error',
                        'alta' => 'badge-error',
                        'media' => 'badge-warning',
                        'baja' => 'badge-info',
                        default => 'badge-secondary'
                    };
                @endphp
                <x-mary-badge :value="ucfirst($lead->urgency_level)" class="badge {{ $urgencyColor }}" />
                @endscope

                @scope('cell_classification', $lead)
                @php
                    $classificationColor = match($lead->classification) {
                        'activo' => 'badge-success',
                        'reciclado' => 'badge-warning',
                        'descartado' => 'badge-error',
                        default => 'badge-secondary'
                    };
                @endphp
                <x-mary-badge :value="ucfirst($lead->classification)" class="badge {{ $classificationColor }}" />
                @endscope

                @scope('cell_funnel_stage', $lead)
                @php
                    $badgeColor = match($lead->funnel_stage) {
                        'ventas' => 'badge-success',
                        'posventa' => 'badge-warning',
                        'fidelización' => 'badge-success',
                        'reactivación' => 'badge-error',
                        default => 'badge-secondary'
                    };
                @endphp
                <x-mary-badge :value="ucfirst($lead->funnel_stage)" class="badge {{ $badgeColor }}" />
                @endscope

                @scope('cell_client_progress', $lead)
                @php
                    $progressColor = match($lead->client_progress) {
                        'sin contactar' => 'badge-warning',
                        'nuevo' => 'badge-info',
                        'contactado' => 'badge-primary',
                        'diagnosticado' => 'badge-secondary',
                        'negociación' => 'badge-warning',
                        'cierre(pendiente adelanto)' => 'badge-info',
                        'vendido' => 'badge-success',
                        'perdido' => 'badge-error',
                        default => 'badge-secondary'
                    };
                @endphp
                <x-mary-badge :value="ucfirst($lead->client_progress)" class="badge {{ $progressColor }}" />
                @endscope

                @scope('cell_probability', $lead)
                @php
                    $probabilityColor = match(true) {
                        $lead->probability >= 80 => 'badge-success',
                        $lead->probability >= 50 => 'badge-warning',
                        $lead->probability >= 20 => 'badge-info',
                        default => 'badge-error'
                    };
                @endphp
                <x-mary-badge :value="$lead->probability . '%'" class="badge {{ $probabilityColor }}" />
                @endscope

                @scope('cell_creat_at', $lead)
                    {{ $lead->creat_at?->format('d/m/Y H:i') ?? 'N/A' }}
                @endscope

                <x-slot:empty>
                    <x-mary-icon name="o-inbox" label="No se encontraron leads." />
                </x-slot:empty>
            </x-mary-table>
        @else
            <div class="text-center py-12">
                <x-mary-icon name="o-building-office-2" label="Selecciona una empresa para ver los leads" />
            </div>
        @endif
    </x-mary-card>

    <x-mary-drawer wire:model="showFilters" title="Filtros" class="lg:w-1/3" right separator with-close-button>
        <div class="grid gap-5">
            @if(auth()->user()->hasRole('admin'))
                <x-mary-select
                        label="Usuario"
                        wire:model.live="userId"
                        :options="$userOptions"
                        option-value="id"
                        option-label="name"
                        placeholder="Todos los usuarios"
                        hint="Leads creados por el usuario o leads de Meta asignados a él (activos e inactivos)"
                        class="select-primary"
                        searchable
                        clearable
                />
            @endif

            <x-mary-select
                    label="Estado"
                    wire:model.live="stateId"
                    :options="$stateOptions"
                    option-value="id"
                    option-label="name"
                    placeholder="Todos los estados"
                    class="select-primary"
                    searchable
                    clearable
            />

            <x-mary-select
                    label="Nivel de Urgencia"
                    wire:model.live="urgencyLevel"
                    :options="[
                        ['id' => 'baja', 'name' => 'Baja'],
                        ['id' => 'media', 'name' => 'Media'],
                        ['id' => 'alta', 'name' => 'Alta'],
                        ['id' => 'inmediata', 'name' => 'Inmediata'],
                    ]"
                    placeholder="Selecciona nivel de urgencia..."
                    class="select-primary"
                    clearable
                    searchable
            />

            <x-mary-select
                    label="Clasificación"
                    wire:model.live="classification"
                    :options="[
                        ['id' => 'activo', 'name' => 'Activo'],
                        ['id' => 'descartado', 'name' => 'Descartado'],
                        ['id' => 'reciclado', 'name' => 'Reciclado'],
                    ]"
                    placeholder="Selecciona clasificación..."
                    class="select-primary"
                    clearable
                    searchable
            />

            <x-mary-select
                    label="Etapa del embudo de ventas"
                    wire:model.live="funnelStage"
                    :options="[
                        ['id' => 'ventas', 'name' => 'Ventas'],
                        ['id' => 'posventa', 'name' => 'Posventa'],
                        ['id' => 'fidelización', 'name' => 'Fidelización'],
                        ['id' => 'reactivación', 'name' => 'Reactivación'],
                    ]"
                    placeholder="Selecciona etapa del embudo..."
                    class="select-primary"
                    clearable
                    searchable
            />

            <x-mary-select
                    label="Fase de ventas"
                    wire:model.live="clientProgress"
                    :options="[
                        ['id' => 'sin contactar', 'name' => 'Sin contactar'],
                        ['id' => 'nuevo', 'name' => 'Nuevo'],
                        ['id' => 'negociación', 'name' => 'Negociación'],
                        ['id' => 'diagnosticado', 'name' => 'Diagnosticado'],
                        ['id' => 'contactado', 'name' => 'Contactado'],
                        ['id' => 'cierre(pendiente adelanto)', 'name' => 'Cierre (pendiente adelanto)'],
                        ['id' => 'vendido', 'name' => 'Vendido'],
                        ['id' => 'perdido', 'name' => 'Perdido'],
                    ]"
                    placeholder="Selecciona fase de ventas..."
                    class="select-primary"
                    clearable
                    searchable
            />

            <x-mary-select
                    label="Estado de Pago"
                    wire:model.live="payStatus"
                    :options="[
                        ['id' => 'Pago pendiente', 'name' => 'Pago pendiente'],
                        ['id' => 'Adelanto', 'name' => 'Adelanto'],
                        ['id' => 'Liquidado', 'name' => 'Liquidado'],
                    ]"
                    placeholder="Selecciona estado de pago..."
                    class="select-primary"
                    clearable
                    searchable
            />

            <x-mary-select
                    label="Post-Venta"
                    wire:model.live="afterSales"
                    :options="[
                        ['id' => 'Espera de producto', 'name' => 'Espera de producto'],
                        ['id' => 'Pedido preparado', 'name' => 'Pedido preparado'],
                        ['id' => 'Pedido enviado', 'name' => 'Pedido enviado'],
                        ['id' => 'Pedido entregado', 'name' => 'Pedido entregado'],
                    ]"
                    placeholder="Selecciona estado de post-venta..."
                    class="select-primary"
                    clearable
                    searchable
            />

            <x-mary-range
                    label="Probabilidad mínima (%)"
                    wire:model.live="probabilityRange"
                    :min="0"
                    :max="100"
                    :step="10"
                    hint="Mostrar leads con probabilidad mayor o igual a {{ $probabilityRange }}%"
            />
            <div class="flex justify-between items-center mt-2">
                <span class="text-xs">0%</span>
                    <span class="text-xs font-bold">
                    {{ $probabilityRange }}%
                </span>
                <span class="text-xs">100%</span>
            </div>

            <x-mary-select
                    label="Estado del Cliente"
                    wire:model.live="clientStatus"
                    :options="[
                        ['id' => 'En pausa', 'name' => 'En pausa'],
                        ['id' => 'Sin respuesta', 'name' => 'Sin respuesta'],
                        ['id' => 'En proceso', 'name' => 'En proceso'],
                    ]"
                    placeholder="Selecciona estado del cliente..."
                    class="select-primary"
                    clearable
                    searchable
            />

            <!-- Canal de Adquisición (Unsemine Channel) -->
            <x-mary-select
                    label="Canal de adquisición"
                    wire:model.live="acquisitionChannel"
                    :options="[
                        ['id' => 'venta fría', 'name' => 'Venta fría'],
                        ['id' => 'meta', 'name' => 'Meta'],
                        ['id' => 'página web', 'name' => 'Página web'],
                    ]"
                    placeholder="Selecciona canal de adquisición..."
                    class="select-primary"
                    clearable
                    searchable
            />
        </div>

        <x-slot:actions>
            <x-mary-button label="Quitar filtros" icon="o-x-mark" wire:click="clear" spinner />
            <x-mary-button label="Aplicar" icon="o-check" class="btn-primary" @click="$wire.showFilters = false" />
        </x-slot:actions>
    </x-mary-drawer>
</section>