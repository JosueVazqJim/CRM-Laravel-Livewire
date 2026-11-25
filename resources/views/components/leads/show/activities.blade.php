@props(['lead'])

<x-mary-card class="shadow-xl">
    <x-slot:title>
        <div class="flex items-center gap-2">
            <x-mary-icon name="o-list-bullet" class="h-5 w-5"></x-mary-icon>
            <span>Actividades</span>
        </div>
    </x-slot:title>

    @if($lead->activities->count() > 0)
        @php
            // Usar los scopes y métodos del modelo para clasificar
            $today = now();

            // Obtener actividades clasificadas directamente desde el modelo
            $overdueActivities = $lead->activities->filter(fn($a) => $a->isOverdue());
            $scheduledActivities = $lead->activities->filter(fn($a) => $a->isFuture());
            $inProgressActivities = $lead->activities->filter(fn($a) =>
                !$a->is_completed &&
                !$a->isOverdue() &&
                !$a->isFuture() &&
                $a->date_action
            );
            $pendingActivities = $lead->activities->filter(fn($a) =>
                !$a->is_completed &&
                !$a->isOverdue() &&
                !$a->isFuture() &&
                !$a->date_action
            );
            $completedActivities = $lead->activities->filter(fn($a) => $a->is_completed);

            // Mapear actividades con información adicional
            $mapActivity = function($activity) use ($today) {
                $statusData = [
                    'id' => $activity->id,
                    'type_action' => $activity->type_action,
                    'description' => $activity->description,
                    'created_at' => $activity->created_at,
                    'scheduled_at' => $activity->scheduled_at,
                    'date_action' => $activity->date_action,
                    'completed_at' => $activity->completed_at,
                ];

                if ($activity->is_completed) {
                    return array_merge($statusData, [
                        'status' => 'completed',
                        'statusDate' => $activity->completed_at,
                        'statusIcon' => 'o-check-circle',
                        'statusColor' => 'success',
                        'statusBorder' => 'border-success',
                    ]);
                }

                if ($activity->isOverdue()) {
                    $diff = $activity->scheduled_at->diff($today);
                    if ($diff->days > 0) {
                        $overdueText = "Vencida hace {$diff->days} día" . ($diff->days > 1 ? 's' : '');
                    } elseif ($diff->hours > 0) {
                        $overdueText = "Vencida hace {$diff->hours} hora" . ($diff->hours > 1 ? 's' : '');
                    } elseif ($diff->minutes > 0) {
                        $overdueText = "Vencida hace {$diff->minutes} minuto" . ($diff->minutes > 1 ? 's' : '');
                    } else {
                        $overdueText = "Vencida recientemente";
                    }

                    return array_merge($statusData, [
                        'status' => 'overdue',
                        'statusDate' => $activity->scheduled_at,
                        'statusIcon' => 'o-exclamation-triangle',
                        'statusColor' => 'error',
                        'statusBorder' => 'border-error',
                        'overdueText' => $overdueText,
                    ]);
                }

                if ($activity->isFuture()) {
                    return array_merge($statusData, [
                        'status' => 'scheduled',
                        'statusDate' => $activity->scheduled_at,
                        'statusIcon' => 'o-calendar',
                        'statusColor' => 'warning',
                        'statusBorder' => 'border-warning',
                    ]);
                }

                if ($activity->date_action) {
                    return array_merge($statusData, [
                        'status' => 'in_progress',
                        'statusDate' => $activity->date_action,
                        'statusIcon' => 'o-play-circle',
                        'statusColor' => 'info',
                        'statusBorder' => 'border-info',
                    ]);
                }

                return array_merge($statusData, [
                    'status' => 'pending',
                    'statusDate' => $activity->created_at,
                    'statusIcon' => 'o-clock',
                    'statusColor' => 'secondary',
                    'statusBorder' => 'border-secondary',
                ]);
            };

            // Crear colecciones mapeadas
            $overdueMapped = $overdueActivities->map($mapActivity);
            $scheduledMapped = $scheduledActivities->map($mapActivity);
            $inProgressMapped = $inProgressActivities->map($mapActivity);
            $pendingMapped = $pendingActivities->map($mapActivity);
            $completedMapped = $completedActivities->map($mapActivity);

            // Combinar pendientes y vencidas
            $pendingOverdue = $pendingMapped->concat($overdueMapped);

            // Configuración del Kanban
            $kanbanColumns = [
                'pending_overdue' => [
                    'title' => 'Pendientes y Vencidas',
                    'icon' => 'o-exclamation-circle',
                    'color' => 'error',
                    'activities' => $pendingOverdue
                ],
                'in_progress' => [
                    'title' => 'En Proceso',
                    'icon' => 'o-play-circle',
                    'color' => 'info',
                    'activities' => $inProgressMapped
                ],
                'scheduled' => [
                    'title' => 'Agendadas',
                    'icon' => 'o-calendar',
                    'color' => 'warning',
                    'activities' => $scheduledMapped
                ],
                'completed' => [
                    'title' => 'Completadas',
                    'icon' => 'o-check-circle',
                    'color' => 'success',
                    'activities' => $completedMapped
                ]
            ];
        @endphp

        {{-- Kanban Board - 4 columnas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($kanbanColumns as $column)
                <div class="bg-base-200 rounded-lg overflow-hidden">
                    {{-- Cabecera de columna --}}
                    <div class="p-3 border-b bg-base-100">
                        <h3 class="font-semibold text-{{ $column['color'] }} flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <x-mary-icon :name="$column['icon']" class="h-4 w-4"></x-mary-icon>
                                <span>{{ $column['title'] }}</span>
                            </div>
                            <span class="badge badge-{{ $column['color'] }} badge-sm">
                                {{ $column['activities']->count() }}
                            </span>
                        </h3>
                    </div>

                    {{-- Tarjetas de actividades --}}
                    <div class="p-3 space-y-2 max-h-[500px] overflow-y-auto">
                        @forelse($column['activities']->sortByDesc('statusDate') as $activity)
                            <div class="bg-base-100 p-3 rounded-lg shadow-sm hover:shadow-md transition-shadow border-l-4 {{ $activity['statusBorder'] }}">
                                {{-- Encabezado de la tarjeta --}}
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <x-mary-icon :name="$activity['statusIcon']" class="h-4 w-4 text-{{ $activity['statusColor'] }}"></x-mary-icon>
                                        <p class="font-medium text-sm">{{ $activity['type_action'] }}</p>
                                    </div>
                                </div>

                                {{-- Descripción --}}
                                @if($activity['description'])
                                    <p class="text-xs text-gray-600 mt-1 mb-2 line-clamp-2">{{ $activity['description'] }}</p>
                                @endif

                                {{-- Información de tiempo --}}
                                <div class="space-y-1 mt-2">
                                    @if($activity['status'] === 'overdue' && isset($activity['overdueText']))
                                        <div class="flex items-center gap-1 text-xs text-error">
                                            <x-mary-icon name="o-clock" class="h-3 w-3"></x-mary-icon>
                                            <span class="font-semibold">{{ $activity['overdueText'] }}</span>
                                        </div>
                                    @endif

                                    @if($activity['scheduled_at'])
                                        <div class="flex items-center gap-1 text-xs text-gray-800 dark:text-gray-400">
                                            <x-mary-icon name="o-calendar" class="h-3 w-3"></x-mary-icon>
                                            <span>Agendada: {{ $activity['scheduled_at']->format('d/m/Y H:i') }}</span>
                                        </div>
                                    @endif

{{--                                    @if($activity['date_action'] && $activity['status'] !== 'completed')--}}
{{--                                        <div class="flex items-center gap-1 text-xs text-gray-800 dark:text-gray-400">--}}
{{--                                            <x-mary-icon name="o-play-circle" class="h-3 w-3"></x-mary-icon>--}}
{{--                                            <span>Inicio: {{ $activity['date_action']->format('d/m/Y H:i') }}</span>--}}
{{--                                        </div>--}}
{{--                                    @endif--}}

                                    @if($activity['status'] === 'completed' && $activity['completed_at'])
                                        <div class="flex items-center gap-1 text-xs text-gray-800 dark:text-gray-400">
                                            <x-mary-icon name="o-check-circle" class="h-3 w-3"></x-mary-icon>
                                            <span>Completada: {{ $activity['completed_at']->format('d/m/Y H:i') }}</span>
                                        </div>
                                    @endif

                                    @if($activity['status'] === 'pending' && $activity['created_at'])
                                        <div class="flex items-center gap-1 text-xs text-gray-800 dark:text-gray-400">
                                            <x-mary-icon name="o-plus-circle" class="h-3 w-3"></x-mary-icon>
                                            <span>Creada: {{ $activity['created_at']->format('d/m/Y H:i') }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Badge de prioridad --}}
                                @if($activity['status'] === 'overdue')
                                    <div class="mt-2">
                                        <span class="badge badge-error badge-xs">Atrasada</span>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-6 text-gray-400 text-xs">
                                <x-mary-icon :name="$column['icon']" class="h-8 w-8 mx-auto mb-2 opacity-30"></x-mary-icon>
                                <p>Sin actividades</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Resumen rápido --}}
        <div class="mt-4 pt-3 border-t flex flex-wrap gap-3 justify-between items-center text-xs text-gray-800 dark:text-gray-400">
            <div class="flex gap-3">
                <span class="flex items-center gap-1">
                    <x-mary-icon name="o-exclamation-triangle" class="h-3 w-3 text-error"></x-mary-icon>
                    Vencidas: {{ $overdueActivities->count() }}
                </span>
                <span class="flex items-center gap-1">
                    <x-mary-icon name="o-play-circle" class="h-3 w-3 text-info"></x-mary-icon>
                    En proceso: {{ $inProgressActivities->count() }}
                </span>
                <span class="flex items-center gap-1">
                    <x-mary-icon name="o-calendar" class="h-3 w-3 text-warning"></x-mary-icon>
                    Agendadas: {{ $scheduledActivities->count() }}
                </span>
                <span class="flex items-center gap-1">
                    <x-mary-icon name="o-clock" class="h-3 w-3 text-secondary"></x-mary-icon>
                    Pendientes: {{ $pendingActivities->count() }}
                </span>
                <span class="flex items-center gap-1">
                    <x-mary-icon name="o-check-circle" class="h-3 w-3 text-success"></x-mary-icon>
                    Completadas: {{ $completedActivities->count() }}
                </span>
            </div>
            <div>
                Total: {{ $lead->activities->count() }} actividades
            </div>
        </div>
    @else
        {{-- Mensaje cuando no hay actividades --}}
        <div class="text-center py-12">
            <x-mary-icon name="o-document-text" class="h-12 w-12 text-gray-300 mx-auto mb-3"></x-mary-icon>
            <p class="text-gray-800 dark:text-gray-400 text-sm">No hay actividades registradas para este lead</p>
            <p class="text-gray-800 dark:text-gray-400 text-xs mt-1">Las actividades aparecerán aquí una vez que se creen</p>
        </div>
    @endif
</x-mary-card>