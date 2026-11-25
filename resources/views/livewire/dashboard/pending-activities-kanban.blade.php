<div class="mt-4 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-5">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <x-mary-icon name="o-list-bullet" class="w-5 h-5 text-primary" />
                Actividades pendientes
            </h2>
            <span class="text-sm text-gray-500 dark:text-gray-400">
                Visión general de tus actividades activas
            </span>
        </div>
    </div>

    @if($totalCount === 0)
        {{-- Estado vacío --}}
        <div class="flex flex-col items-center gap-3 py-12 text-gray-400 dark:text-gray-500">
            <x-mary-icon name="o-check-badge" class="w-12 h-12 opacity-40" />
            <p class="text-sm font-medium">Sin actividades pendientes</p>
            <p class="text-xs">Todas las actividades de tus leads están al día.</p>
        </div>
    @else
        {{-- Kanban: 3 columnas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($kanbanColumns as $column)
                <div class="bg-base-200 dark:bg-neutral-800 rounded-xl overflow-hidden">

                    {{-- Cabecera de columna --}}
                    <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-700 bg-base-100 dark:bg-neutral-900">
                        <h3 class="font-semibold text-{{ $column['color'] }} flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <x-mary-icon :name="$column['icon']" class="h-4 w-4" />
                                <span>{{ $column['title'] }}</span>
                            </div>
                            <span class="badge badge-{{ $column['color'] }} badge-sm">
                                {{ $column['activities']->count() }}
                            </span>
                        </h3>
                    </div>

                    {{-- Tarjetas --}}
                    <div class="p-3 space-y-2 max-h-[420px] overflow-y-auto">
                        @forelse($column['activities'] as $activity)
                            <div class="bg-base-100 dark:bg-neutral-900 p-3 rounded-lg shadow-sm hover:shadow-md transition-shadow border-l-4 {{ $activity['statusBorder'] }}">

                                {{-- Tipo de acción + icono --}}
                                <div class="flex items-center gap-2 mb-1">
                                    <x-mary-icon :name="$activity['statusIcon']" class="h-4 w-4 flex-shrink-0 text-{{ $activity['statusColor'] }}" />
                                    <p class="font-medium text-sm truncate text-gray-800 dark:text-gray-100">
                                        {{ $activity['type_action'] }}
                                    </p>
                                </div>

                                {{-- Descripción --}}
                                @if($activity['description'])
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 line-clamp-2">
                                        {{ $activity['description'] }}
                                    </p>
                                @endif

                                {{-- Info de tiempo --}}
                                <div class="space-y-1 mb-2">
                                    @if($activity['status'] === 'overdue')
                                        <div class="flex items-center gap-1 text-xs text-error font-semibold">
                                            <x-mary-icon name="o-clock" class="h-3 w-3 flex-shrink-0" />
                                            <span>{{ $activity['overdueText'] }}</span>
                                        </div>
                                    @endif

                                    @if($activity['scheduled_at'])
                                        <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                            <x-mary-icon name="o-calendar" class="h-3 w-3 flex-shrink-0" />
                                            <span>{{ $activity['scheduled_at']->format('d/m/Y H:i') }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Lead (enlace) --}}
                                <div class="pt-2 border-t border-neutral-100 dark:border-neutral-700">
                                    <a
                                        href="{{ route('leads.show', $activity['lead_id']) }}"
                                        wire:navigate
                                        class="flex items-center gap-1 text-xs text-primary hover:underline font-medium truncate"
                                        title="{{ $activity['lead_name'] }}"
                                    >
                                        <x-mary-icon name="o-arrow-top-right-on-square" class="h-3 w-3 flex-shrink-0" />
                                        <span class="truncate">{{ $activity['lead_name'] }}</span>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center py-8 text-gray-400 dark:text-gray-500 text-xs gap-2">
                                <x-mary-icon :name="$column['icon']" class="h-8 w-8 opacity-30" />
                                <span>Sin actividades</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
