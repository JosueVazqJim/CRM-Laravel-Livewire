{{-- resources/views/livewire/leads/components-show/quotes-index.blade.php --}}
<div class="card bg-base-100 shadow-sm">
    <div class="card-body">
        {{-- Header con estadísticas --}}
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-4">
            <h2 class="card-title text-xl">
                Cotizaciones
                <div class="badge badge-primary badge-sm">{{ $stats['total'] }}</div>
            </h2>

            {{-- Botón para nueva cotización --}}
            <x-mary-button label="Nueva Cotización" icon="o-plus" :link="route('quotes.create', $lead)"
                           class="btn-primary btn-sm" responsive />
        </div>

        {{-- Tarjetas de estadísticas --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
            <div class="stat bg-base-200 rounded-box p-3">
                <div class="stat-title text-xs">Total</div>
                <div class="stat-value text-gray-800 dark:text-gray-400 text-2xl">{{ $stats['total'] }}</div>
            </div>
            <div class="stat bg-base-200 rounded-box p-3">
                <div class="stat-title text-xs">En Proceso</div>
                <div class="stat-value text-info text-2xl">{{ $stats['total_in_process'] }}</div>
            </div>
            <div class="stat bg-base-200 rounded-box p-3">
                <div class="stat-title text-xs">En Validación</div>
                <div class="stat-value text-warning text-2xl">{{ $stats['total_in_validation'] }}</div>
            </div>
            <div class="stat bg-base-200 rounded-box p-3">
                <div class="stat-title text-xs">Exitosas</div>
                <div class="stat-value text-success text-2xl">{{ $stats['total_approved'] }}</div>
            </div>
            <div class="stat bg-base-200 rounded-box p-3">
                <div class="stat-title text-xs">Sin Éxito</div>
                <div class="stat-value text-error text-2xl">{{ $stats['total_rejected'] }}</div>
            </div>
            <div class="stat bg-base-200 rounded-box p-3">
                <div class="stat-title text-xs">Canceladas</div>
                <div class="stat-value text-neutral dark:text-white text-2xl">{{ $stats['total_cancelled'] }}</div>
            </div>
            <div class="stat bg-base-200 rounded-box p-3">
                <div class="stat-title text-xs">Monto Total</div>
                <div class="stat-value text-warning text-xl">{{ number_format($stats['total_amount'], 2) }} USD</div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="flex flex-wrap gap-3 mb-4">
            <x-mary-select
                    label="Ver cotizaciones de"
                    wire:model.live="userFilter"
                    :options="$userFilterOptions"
                    class="w-56"
            />

            <x-mary-input
                    label="Buscar"
                    placeholder="Folio o empresa..."
                    wire:model.live.debounce.300ms="search"
                    icon="o-magnifying-glass"
                    class="w-64"
            />

            <x-mary-select
                    label="Estado"
                    placeholder="Todos"
                    wire:model.live="statusFilter"
                    :options="$statusOptions"
                    class="w-48"
            />

            <x-mary-select
                    label="Tipo de Versión"
                    wire:model.live="versionType"
                    :options="$versionOptions"
                    class="w-56"
            />

            <x-mary-select
                    label="Ordenar por"
                    wire:model.live="sortBy"
                    :options="$sortByOptions"
                    class="w-48"
            />

            @if($search || $statusFilter || $versionType !== 'all' || $sortBy !== 'created_at_desc')
                <button wire:click="resetFilters" class="btn btn-ghost btn-sm mt-7">
                    <x-mary-icon name="o-x-mark" class="w-4 h-4" />
                    Limpiar filtros
                </button>
            @endif
        </div>

        {{-- Tabla de cotizaciones --}}
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                <tr>
                    <th>Folio</th>
                    <th>Versión</th>
                    <th>Emisor</th>
                    <th>Estado</th>
                    <th>Total</th>
{{--                    <th>Tasa de Cambio</th>--}}
                    <th>Fecha</th>
                    <th class="text-center">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse($quotes as $quote)
                    @php
                        $badge = $this->getStatusBadge($quote->status);
                        $hasRecotizacion = $quote->source_version_id !== null;
                        $exchangeRateUsed = $quote->exchangeRates->first();
                    @endphp
                    <tr class="hover">
                        <td class="font-mono text-sm">
                            {{ $quote->folio }}
                        </td>
                        <td>
                            <div class="badge badge-outline">Versión {{ $quote->version_letter }}</div>
                            @if($quote->source_version_id)
                                <div class="text-xs text-gray-500">Base: {{ $quote->parentVersionQuote->folio ?? 'N/A' }}</div>
                            @endif
                        </td>
                        <td class="text-sm">
                            {{ $quote->quote->user->name ?? 'N/A' }}
                        </td>
                        <td>
                            <div class="badge {{ $badge['class'] }} badge-sm">
                                {{ $badge['label'] }}
                            </div>
                        </td>
                        <td class="font-bold">${{ number_format($quote->total, 2) }}</td>
{{--                        <td>--}}
{{--                            @if($exchangeRateUsed)--}}
{{--                                <span class="text-xs font-mono">--}}
{{--                                        {{ number_format($exchangeRateUsed->rate, 4) }} {{ $exchangeRateUsed->to_currency }}--}}
{{--                                    </span>--}}
{{--                            @else--}}
{{--                                <span class="text-xs text-gray-400">-</span>--}}
{{--                            @endif--}}
{{--                        </td>--}}
                        <td class="text-sm">
                            {{ $quote->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            <div class="flex justify-center gap-1">
                                {{-- Ver detalles --}}
                                <x-mary-button label="" icon="o-eye" :link="route('quotes.show', $quote->id)" class="btn btn-ghost btn-xs" responsive />


                                {{-- Cambiar estado (dropdown) --}}
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">
                                        <x-mary-icon name="o-adjustments-horizontal" class="w-4 h-4" />
                                    </label>
                                    <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-10 w-36 shadow border">

                                        @if(in_array($quote->status, ['en_proceso', 'en_validacion', 'exitosa', 'sin_exito', 'cancelada', 'papelera']))
                                                <li><a wire:click="changeStatus({{ $quote->id }}, 'en_proceso')">En proceso</a></li>
                                                <li><a wire:click="changeStatus({{ $quote->id }}, 'en_validacion')">Enviar a validación</a></li>
                                                <li><a wire:click="changeStatus({{ $quote->id }}, 'exitosa')">Exitosa</a></li>
                                                <li><a wire:click="changeStatus({{ $quote->id }}, 'sin_exito')">Sin éxito</a></li>
                                                <li><a wire:click="changeStatus({{ $quote->id }}, 'cancelada')">Cancelar</a></li>
                                                <li><a wire:click="changeStatus({{ $quote->id }}, 'papelera')">Enviar a papelera</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500">
                            <x-mary-icon name="o-document-text" class="w-12 h-12 mx-auto mb-2 opacity-50" />
                            <p>No hay cotizaciones para este lead</p>
                            <x-mary-button label="Crear primera cotización" icon="o-plus" :link="route('quotes.create', $lead)"
                                           class="btn-primary btn-sm" responsive />
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="mt-4">
            {{ $quotes->links() }}
        </div>
    </div>
</div>