@php
    $pipeline = $this->totalPipeLineThisMonth();
    $percentage = $this->porcentajePipeLineLastMonth();

    $isPositive = $percentage > 0;
    $isNegative = $percentage < 0;

    $textColor = $isPositive
        ? 'text-emerald-600 dark:text-emerald-400'
        : ($isNegative
            ? 'text-red-600 dark:text-red-400'
            : 'text-gray-500 dark:text-gray-400');

    $bgColor = $isPositive
        ? 'bg-emerald-100 dark:bg-emerald-500/10'
        : ($isNegative
            ? 'bg-red-100 dark:bg-red-500/10'
            : 'bg-gray-100 dark:bg-gray-500/10');
@endphp

<section class="w-full">
    <div class="relative mb-6 w-full">
        <x-mary-header title="Buen día, {{ auth()->user()->name }} 👋"
                       subtitle="{{ now()->locale('es')->translatedFormat('l j \de F \de Y') }} | Aquí va el resumen de hoy"
                       separator progress-indicator>
            <x-slot:actions>
                <div class="flex items-center gap-3">
                    {{-- Avatar de la company --}}
                    <x-mary-avatar
                            :image="$this->selectedCompany?->avatar_url ?? asset('images/empty-company.jpg')"
                            class="!w-36 !h-16 !rounded-lg object-cover shadow-sm {{ !$this->selectedCompany ? 'opacity-50 grayscale' : '' }}"
                    />

                    {{-- Choices --}}
                    @if(count($companiesOptions) > 1)
                        <x-mary-choices-offline
                                wire:model.live="selectedCompanyId"
                                :options="$companiesOptions"
                                placeholder="Buscar empresa..."
                                no-result-text="No se encontraron empresas"
                                single
                                searchable
                                class="min-w-64 select-primary"
                        />
                    @elseif(count($companiesOptions) === 1)
                        <span class="badge badge-outline gap-1">
            <x-mary-icon name="o-building-office" class="w-3 h-3"/>
            {{ $companiesOptions[0]['name'] }}
        </span>
                    @endif
                </div>
            </x-slot:actions>
        </x-mary-header>

        @if(empty($companiesOptions))
            <x-mary-alert
                title="Sin empresas asignadas"
                description="No tienes empresas asignadas. Contacta al administrador."
                icon="o-exclamation-triangle"
                class="alert-warning"
            />
        @else

        {{-- Barra de modos de vista (solo admin) --}}
        @if(auth()->user()->hasRole('admin'))
        <div class="flex overflow-x-auto items-center gap-3 mb-4 p-3
                    bg-white dark:bg-neutral-900
                    rounded-2xl border border-neutral-200 dark:border-neutral-700">

            <x-mary-button
                label="Mi info"
                icon="o-user"
                wire:click="setViewMode('mine')"
                class="{{ $adminViewMode === 'mine' ? 'btn-primary' : 'btn-ghost' }} btn-sm"
            />

            <x-mary-button
                label="Vista general"
                icon="o-building-office"
                wire:click="setViewMode('general')"
                class="{{ $adminViewMode === 'general' ? 'btn-primary' : 'btn-ghost' }} btn-sm"
            />

            <div class="w-px h-6 bg-neutral-200 dark:bg-neutral-700"></div>

            <div class="flex items-center gap-2">
                <x-mary-button
                    label="Ver por usuario"
                    icon="o-users"
                    wire:click="setViewMode('user')"
                    class="{{ $adminViewMode === 'user' ? 'btn-primary' : 'btn-ghost' }} btn-sm"
                />
                @if($adminViewMode === 'user')
                    <x-mary-select
                        wire:model.live="viewAsUserId"
                        :options="$viewAsUserOptions"
                        icon="o-user-circle"
                        class="min-w-44"
                    />
                @endif
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-2 xl:grid-cols-4">

            {{-- Pipeline mensual --}}
            <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Pipeline del mes
                        </p>

                        <h3 wire:click="openPipelineModal"
                            class="mt-2 text-3xl font-bold text-gray-900 dark:text-white cursor-pointer hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                            {{$this->totalPipeLineThisMonth()}} USD
                        </h3>

                        <p class="mt-2 text-xs {{ $textColor }}">
                            {{ $percentage }}% respecto al mes pasado
                        </p>
                    </div>

                    <div class="rounded-xl {{ $bgColor }} p-3">
                        <x-heroicon-o-banknotes class="w-6 h-6 {{ $textColor }}"/>
                    </div>
                </div>
            </div>

            {{-- Leads nuevos --}}
            <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Leads nuevos esta semana
                        </p>

                        <h3 wire:click="openNewLeadsModal('week')"
                            class="mt-2 text-3xl font-bold text-gray-900 dark:text-white cursor-pointer hover:text-sky-600 dark:hover:text-sky-400 transition-colors">
                            {{ $this->countLeadsThisWeek() }}
                        </h3>

                        <p wire:click="openNewLeadsModal('today')"
                           class="mt-2 text-xs text-sky-600 dark:text-sky-400 cursor-pointer hover:underline">
                            {{ $this->countLeadsToday() }} llegaron hoy
                        </p>
                    </div>

                    <div class="rounded-xl bg-sky-100 dark:bg-sky-500/10 p-3">
                        <x-heroicon-o-user-plus class="w-6 h-6 text-sky-600 dark:text-sky-400"/>
                    </div>
                </div>
            </div>

            {{-- Tasa de conversión --}}
            @php $conversion = $this->conversionStats; @endphp
            <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Tasa de conversión
                            <span class="text-xs">({{ \App\Livewire\Dash::METRICS_WINDOW_DAYS }} días)</span>
                        </p>

                        <h3 wire:click="openConversionModal('all')"
                            class="mt-2 text-3xl font-bold text-gray-900 dark:text-white cursor-pointer hover:text-violet-600 dark:hover:text-violet-400 transition-colors">
                            {{ $conversion['rate'] !== null ? $conversion['rate'].'%' : '—' }}
                        </h3>

                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            <span wire:click="openConversionModal('exitosa')"
                                  class="text-emerald-600 dark:text-emerald-400 cursor-pointer hover:underline">{{ $conversion['won'] }} {{ $conversion['won'] === 1 ? 'ganada' : 'ganadas' }}</span>
                            ·
                            <span wire:click="openConversionModal('sin_exito')"
                                  class="text-red-600 dark:text-red-400 cursor-pointer hover:underline">{{ $conversion['lost'] }} {{ $conversion['lost'] === 1 ? 'perdida' : 'perdidas' }}</span>
                        </p>
                    </div>

                    <div class="rounded-xl bg-violet-100 dark:bg-violet-500/10 p-3">
                        <x-heroicon-o-arrow-trending-up class="w-6 h-6 text-violet-600 dark:text-violet-400"/>
                    </div>
                </div>
            </div>

            {{-- Cotizaciones abiertas --}}
            @php $openQuotes = $this->openQuotesStats; @endphp
            <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Cotizaciones abiertas
                        </p>

                        <h3 wire:click="openOpenQuotesModal"
                            class="mt-2 text-3xl font-bold text-gray-900 dark:text-white cursor-pointer hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                            {{ $openQuotes['count'] }}
                            <span class="text-base font-medium text-gray-500 dark:text-gray-400">
                                · ${{ number_format($openQuotes['amount'], 0) }} USD
                            </span>
                        </h3>

                        <p class="mt-2 text-xs text-amber-600 dark:text-amber-400">
                            Ticket promedio ganado: ${{ number_format($conversion['avg_ticket'], 2) }} USD
                        </p>
                    </div>

                    <div class="rounded-xl bg-amber-100 dark:bg-amber-500/10 p-3">
                        <x-heroicon-o-document-text class="w-6 h-6 text-amber-600 dark:text-amber-400"/>
                    </div>
                </div>
            </div>

        </div>
            <livewire:dashboard.pending-activities-kanban />


            <div class="grid auto-rows-min gap-4 md:grid-cols-2">
            <div class="relative overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-5">
                <div class="relative z-10 p-4">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Leads por etapa</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Conteo de leads en cada etapa</span>

                    <x-mary-chart wire:model="leadsByStageChart" />
                </div>
            </div>
            <div class="relative overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-5">
                <div class="relative z-10 p-4 space-y-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                                Pipeline por vendedor
                            </h2>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                Cotizaciones por etapa
                            </span>
                        </div>

                        @if(auth()->user()->hasRole('admin') && $adminViewMode === 'general')
                            <div class="w-48">
                                <x-mary-select
                                    wire:model.live="switchUserPipeline"
                                    :options="$usersOptions"
                                    icon="o-user"
                                />
                            </div>
                        @endif
                    </div>

                    <x-mary-chart wire:model="quotesStageByUserChart" />
                </div>
            </div>
        </div>

        <div class="grid gap-4 mt-4 lg:grid-cols-3">
            <div class="relative overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-5 lg:col-span-2">
                <div class="relative z-10 p-4">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Ingresos mensuales</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Total en USD de cotizaciones exitosas por mes (últimos 6 meses)</span>
                    <x-mary-chart wire:model="salesPerMonthChart" />
                </div>
            </div>

            <div class="relative overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-5">
                <div class="relative z-10 p-4">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Leads por canal</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Origen de los leads</span>
                    <div class="mx-auto mt-2 max-w-xs">
                        <x-mary-chart wire:model="leadsByChannelChart" />
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-4 mt-4 lg:grid-cols-2">
            {{-- Productos más vendidos --}}
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-5">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Productos más vendidos</h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">Histórico por ingreso en cotizaciones exitosas (USD, con descuento)</span>

                @if($this->topProducts->isEmpty())
                    <p class="py-8 text-center text-sm text-gray-400 dark:text-gray-500">Aún no hay ventas registradas.</p>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-right">Unidades</th>
                                    <th class="text-right">Cotizaciones</th>
                                    <th class="text-right">Ingreso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->topProducts as $row)
                                    <tr wire:key="top-product-{{ $row->product_id }}">
                                        <td class="max-w-56">
                                            <p class="truncate font-medium">{{ $row->product?->name ?? 'Producto eliminado' }}</p>
                                            <p class="truncate font-mono text-xs text-gray-500 dark:text-gray-400">{{ $row->product?->sku }}</p>
                                        </td>
                                        <td class="text-right">{{ number_format($row->units, 0) }}</td>
                                        <td class="text-right">{{ $row->quotes_count }}</td>
                                        <td class="text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                            ${{ number_format($row->revenue, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Cotizaciones sin movimiento --}}
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-5">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Cotizaciones sin movimiento</h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Abiertas y sin cambios en más de {{ \App\Livewire\Dash::STALE_QUOTE_DAYS }} días
                </span>

                @if($this->staleQuotes->isEmpty())
                    <p class="py-8 text-center text-sm text-gray-400 dark:text-gray-500">Todo al día 🎉</p>
                @else
                    <div class="mt-4 space-y-2">
                        @foreach($this->staleQuotes as $staleVersion)
                            <a href="{{ route('quotes.show', $staleVersion->id) }}"
                               wire:navigate
                               wire:key="stale-{{ $staleVersion->id }}"
                               class="flex items-center justify-between gap-3 rounded-xl border border-neutral-100 dark:border-neutral-700 bg-base-100 p-3 transition-colors hover:bg-base-200">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $staleVersion->quote?->lead?->name_company ?? '—' }}
                                    </p>
                                    <p class="truncate font-mono text-xs text-gray-500 dark:text-gray-400">
                                        {{ $staleVersion->folio }}
                                        @if($staleVersion->quote?->user)
                                            · {{ $staleVersion->quote->user->first_name }}
                                        @endif
                                    </p>
                                </div>
                                <div class="flex-shrink-0 text-right">
                                    <p class="text-sm font-semibold">${{ number_format($staleVersion->total, 2) }}</p>
                                    <span class="badge badge-sm badge-warning">
                                        {{ (int) $staleVersion->updated_at->diffInDays(now()) }} días
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Ranking de vendedores (solo admin, vista general) --}}
        @if(auth()->user()->hasRole('admin') && $adminViewMode === 'general')
            <div class="mt-4 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-5">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Ranking de vendedores</h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Cotizaciones cerradas en los últimos {{ \App\Livewire\Dash::METRICS_WINDOW_DAYS }} días
                </span>

                @if($this->sellerRanking->isEmpty())
                    <p class="py-8 text-center text-sm text-gray-400 dark:text-gray-500">Sin cotizaciones cerradas en el periodo.</p>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Vendedor</th>
                                    <th class="text-right">Ganadas</th>
                                    <th class="text-right">Perdidas</th>
                                    <th class="min-w-40">Conversión</th>
                                    <th class="text-right">Monto ganado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->sellerRanking as $position => $seller)
                                    <tr wire:key="seller-{{ $seller['user']?->id ?? $position }}">
                                        <td class="font-semibold text-gray-500">{{ $position + 1 }}</td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <div class="avatar">
                                                    <div class="w-8 rounded-full">
                                                        <img src="{{ $seller['user']?->avatar_url ?? asset('images/empty-user.jpg') }}" alt="" />
                                                    </div>
                                                </div>
                                                <span class="font-medium">{{ $seller['user']?->name ?? 'Usuario eliminado' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-right text-emerald-600 dark:text-emerald-400">{{ $seller['won'] }}</td>
                                        <td class="text-right text-red-600 dark:text-red-400">{{ $seller['lost'] }}</td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <progress class="progress progress-primary w-24" value="{{ $seller['rate'] }}" max="100"></progress>
                                                <span class="text-xs">{{ $seller['rate'] }}%</span>
                                            </div>
                                        </td>
                                        <td class="text-right font-semibold">${{ number_format($seller['amount'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        @endif
    </div>

    {{-- Modal: leads nuevos --}}
    <x-mary-modal wire:model="showNewLeadsModal"
        :title="$newLeadsModalPeriod === 'week' ? 'Leads nuevos esta semana' : 'Leads que llegaron hoy'">

        @if($showNewLeadsModal)
            @php $modalLeads = $this->newLeadsModalList() @endphp

            @if($modalLeads->isEmpty())
                <div class="flex flex-col items-center gap-3 py-10 text-gray-400 dark:text-gray-500">
                    <x-mary-icon name="o-inbox" class="w-12 h-12" />
                    <p class="text-sm">No hay leads en este período.</p>
                </div>
            @else
                <div class="space-y-2 max-h-[60vh] overflow-y-auto pr-1">
                    @foreach($modalLeads as $lead)
                        <a href="{{ route('leads.show', $lead) }}"
                           class="flex items-center justify-between p-3 rounded-xl border border-neutral-100 dark:border-neutral-700 bg-base-100 hover:bg-base-200 transition-colors">

                            {{-- Empresa + nombre --}}
                            <div class="flex items-center gap-3 min-w-0">
                                <x-mary-avatar
                                    :image="$lead->company?->avatar_url ?? asset('images/empty-company.jpg')"
                                    class="!w-10 !h-10 !rounded-lg flex-shrink-0"
                                />
                                <div class="min-w-0">
                                    <p class="font-medium text-sm text-gray-900 dark:text-white truncate">
                                        {{ $lead->name_company ?? '—' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ ucfirst($lead->funnel_stage ?? '—') }}
                                    </p>
                                </div>
                            </div>

                            {{-- Fecha + usuario --}}
                            <div class="text-right flex-shrink-0 ml-4">
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $lead->creat_at?->format('d/m/Y H:i') ?? '—' }}
                                </p>
                                @if($lead->user)
                                    <p class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                        {{ $lead->user->first_name }} {{ $lead->user->paternal_surname }}
                                    </p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        @endif

        <x-slot:actions>
            <x-mary-button label="Cerrar" wire:click="$set('showNewLeadsModal', false)" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Modal: pipeline del mes --}}
    @php
        $pipelineModalTitle = 'Cotizaciones que generaron el pipeline del mes';

        if (auth()->user()->hasRole('admin')) {
            $pipelineModalTitle = match($adminViewMode) {
                'mine'    => 'Mis cotizaciones del mes',
                'user'    => 'Cotizaciones del mes de '.(collect($viewAsUserOptions)->firstWhere('id', $viewAsUserId)['name'] ?? 'usuario'),
                default   => 'Cotizaciones del mes (todos los vendedores)',
            };
        }
    @endphp
    <x-mary-modal wire:model="showPipelineModal" :title="$pipelineModalTitle">

        @if($showPipelineModal)
            @php $pipelineQuotes = $this->pipelineModalList() @endphp

            @if($pipelineQuotes->isEmpty())
                <div class="flex flex-col items-center gap-3 py-10 text-gray-400 dark:text-gray-500">
                    <x-mary-icon name="o-inbox" class="w-12 h-12" />
                    <p class="text-sm">No hay cotizaciones exitosas este mes.</p>
                </div>
            @else
                <div class="space-y-2 max-h-[60vh] overflow-y-auto pr-1">
                    @foreach($pipelineQuotes as $quoteVersion)
                        <a href="{{ route('quotes.show', $quoteVersion->id) }}"
                           class="flex items-center justify-between p-3 rounded-xl border border-neutral-100 dark:border-neutral-700 bg-base-100 hover:bg-base-200 transition-colors">

                            {{-- Lead + folio --}}
                            <div class="flex items-center gap-3 min-w-0">
                                <x-mary-avatar
                                    :image="$quoteVersion->quote?->company?->avatar_url ?? asset('images/empty-company.jpg')"
                                    class="!w-10 !h-10 !rounded-lg flex-shrink-0"
                                />
                                <div class="min-w-0">
                                    <p class="font-medium text-sm text-gray-900 dark:text-white truncate">
                                        {{ $quoteVersion->quote?->lead?->name_company ?? '—' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                                        {{ $quoteVersion->folio }}
                                    </p>
                                </div>
                            </div>

                            {{-- Total + fecha + usuario emisor --}}
                            <div class="text-right flex-shrink-0 ml-4">
                                <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                    ${{ number_format($quoteVersion->total, 2) }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $quoteVersion->closed_at?->format('d/m/Y H:i') ?? '—' }}
                                </p>
                                @if($quoteVersion->quote?->user)
                                    <p class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                        {{ $quoteVersion->quote->user->name }}
                                    </p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        @endif

        <x-slot:actions>
            <x-mary-button label="Cerrar" wire:click="$set('showPipelineModal', false)" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Modal: tasa de conversión --}}
    <x-mary-modal wire:model="showConversionModal"
        title="Cotizaciones cerradas (últimos {{ \App\Livewire\Dash::METRICS_WINDOW_DAYS }} días)">

        @if($showConversionModal)
            @php $closedVersions = $this->conversionModalList() @endphp

            {{-- Filtro --}}
            <div class="mb-3 flex gap-2">
                @foreach(['all' => 'Todas', 'exitosa' => 'Ganadas', 'sin_exito' => 'Perdidas'] as $filterKey => $filterLabel)
                    <x-mary-button :label="$filterLabel"
                        wire:click="$set('conversionModalFilter', '{{ $filterKey }}')"
                        class="btn-xs {{ $conversionModalFilter === $filterKey ? 'btn-primary' : 'btn-ghost' }}" />
                @endforeach
            </div>

            @if($closedVersions->isEmpty())
                <div class="flex flex-col items-center gap-3 py-10 text-gray-400 dark:text-gray-500">
                    <x-mary-icon name="o-inbox" class="w-12 h-12" />
                    <p class="text-sm">No hay cotizaciones cerradas en este período.</p>
                </div>
            @else
                <div class="space-y-2 max-h-[60vh] overflow-y-auto pr-1">
                    @foreach($closedVersions as $closedVersion)
                        @php $won = $closedVersion->status === \App\Models\Quotes\QuoteVersion::STATUS_EXITOSA; @endphp
                        <a href="{{ route('quotes.show', $closedVersion->id) }}"
                           wire:key="conversion-{{ $closedVersion->id }}"
                           class="flex items-center justify-between p-3 rounded-xl border border-neutral-100 dark:border-neutral-700 bg-base-100 hover:bg-base-200 transition-colors">

                            {{-- Lead + folio --}}
                            <div class="flex items-center gap-3 min-w-0">
                                <x-mary-avatar
                                    :image="$closedVersion->quote?->company?->avatar_url ?? asset('images/empty-company.jpg')"
                                    class="!w-10 !h-10 !rounded-lg flex-shrink-0"
                                />
                                <div class="min-w-0">
                                    <p class="font-medium text-sm text-gray-900 dark:text-white truncate">
                                        {{ $closedVersion->quote?->lead?->name_company ?? '—' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono truncate">
                                        {{ $closedVersion->folio }}
                                    </p>
                                </div>
                            </div>

                            {{-- Estado + total + fecha + vendedor --}}
                            <div class="text-right flex-shrink-0 ml-4">
                                <span class="badge badge-sm {{ $won ? 'badge-success' : 'badge-error' }} text-white">
                                    {{ $won ? 'Ganada' : 'Perdida' }}
                                </span>
                                <p class="text-sm font-bold {{ $won ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500 dark:text-gray-400 line-through' }}">
                                    ${{ number_format($closedVersion->total, 2) }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $closedVersion->updated_at?->format('d/m/Y') ?? '—' }}
                                    @if($closedVersion->quote?->user)
                                        · {{ $closedVersion->quote->user->first_name }}
                                    @endif
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        @endif

        <x-slot:actions>
            <x-mary-button label="Cerrar" wire:click="$set('showConversionModal', false)" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Modal: cotizaciones abiertas --}}
    <x-mary-modal wire:model="showOpenQuotesModal" title="Cotizaciones abiertas">

        @if($showOpenQuotesModal)
            @php $openVersions = $this->openQuotesModalList() @endphp

            @if($openVersions->isEmpty())
                <div class="flex flex-col items-center gap-3 py-10 text-gray-400 dark:text-gray-500">
                    <x-mary-icon name="o-inbox" class="w-12 h-12" />
                    <p class="text-sm">No hay cotizaciones abiertas.</p>
                </div>
            @else
                <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">
                    En proceso o en validación, ordenadas de la más antigua a la más reciente sin cambios.
                </p>

                <div class="space-y-2 max-h-[60vh] overflow-y-auto pr-1">
                    @foreach($openVersions as $openVersion)
                        @php
                            $idleDays = (int) $openVersion->updated_at->diffInDays(now());
                            $isStale = $idleDays > \App\Livewire\Dash::STALE_QUOTE_DAYS;
                        @endphp
                        <a href="{{ route('quotes.show', $openVersion->id) }}"
                           wire:key="open-quote-{{ $openVersion->id }}"
                           class="flex items-center justify-between p-3 rounded-xl border border-neutral-100 dark:border-neutral-700 bg-base-100 hover:bg-base-200 transition-colors">

                            {{-- Lead + folio --}}
                            <div class="flex items-center gap-3 min-w-0">
                                <x-mary-avatar
                                    :image="$openVersion->quote?->company?->avatar_url ?? asset('images/empty-company.jpg')"
                                    class="!w-10 !h-10 !rounded-lg flex-shrink-0"
                                />
                                <div class="min-w-0">
                                    <p class="font-medium text-sm text-gray-900 dark:text-white truncate">
                                        {{ $openVersion->quote?->lead?->name_company ?? '—' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono truncate">
                                        {{ $openVersion->folio }}
                                        @if($openVersion->quote?->user)
                                            · {{ $openVersion->quote->user->first_name }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            {{-- Estado + total + días sin movimiento --}}
                            <div class="text-right flex-shrink-0 ml-4">
                                <span class="badge badge-sm badge-outline">
                                    {{ $openVersion->status === \App\Models\Quotes\QuoteVersion::STATUS_EN_VALIDACION ? 'En validación' : 'En proceso' }}
                                </span>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">
                                    ${{ number_format($openVersion->total, 2) }}
                                </p>
                                <p class="text-xs {{ $isStale ? 'text-amber-600 dark:text-amber-400 font-medium' : 'text-gray-500 dark:text-gray-400' }}">
                                    {{ $idleDays === 0 ? 'Actualizada hoy' : "Sin cambios hace {$idleDays} ".($idleDays === 1 ? 'día' : 'días') }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        @endif

        <x-slot:actions>
            <x-mary-button label="Cerrar" wire:click="$set('showOpenQuotesModal', false)" />
        </x-slot:actions>
    </x-mary-modal>
</section>
