<div>
    <x-mary-card class="shadow-xl">
        <x-slot:title>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-mary-icon name="o-list-bullet" class="h-5 w-5" />
                    <span>Actividades</span>
                </div>
                <x-mary-button
                        icon="o-plus"
                        label="Nueva actividad"
                        class="btn-sm btn-primary"
                        wire:click="createActivity"
                />
            </div>
        </x-slot:title>

        @if($activities->count() > 0)
            {{-- Kanban Board - 4 columnas --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($kanbanColumns as $key => $column)
                    <div class="bg-base-200 rounded-lg overflow-hidden">
                        {{-- Cabecera de columna --}}
                        <div class="p-3 border-b bg-base-100">
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

                        {{-- Tarjetas de actividades --}}
                        <div class="p-3 space-y-2 max-h-[500px] overflow-y-auto">
                            @forelse($column['activities']->sortByDesc('statusDate') as $activity)
                                <div class="bg-base-100 p-3 rounded-lg shadow-sm hover:shadow-md transition-all border-l-4 {{ $activity->statusBorder }} group">
                                    {{-- Encabezado con acciones --}}
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex items-center gap-2 flex-1">
                                            <x-mary-icon :name="$activity->statusIcon" class="h-4 w-4 text-{{ $activity->statusColor }}" />
                                            <p class="font-medium text-sm">{{ $activity->type_action }}</p>
                                        </div>

                                        {{-- Botones de acción --}}
                                        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
{{--                                            @if($activity->status === 'pending')--}}
{{--                                                <button wire:click="startActivity({{ $activity->id }})"--}}
{{--                                                        class="btn btn-xs btn-ghost text-info"--}}
{{--                                                        title="Iniciar">--}}
{{--                                                    <x-mary-icon name="o-play" class="h-3 w-3" />--}}
{{--                                                </button>--}}
{{--                                            @endif--}}

                                            @if($activity->status !== 'completed')
                                                <button wire:click="toggleComplete({{ $activity->id }})"
                                                        class="btn btn-xs btn-ghost text-success"
                                                        title="Completar">
                                                    <x-mary-icon name="o-check" class="h-3 w-3" />
                                                </button>
                                            @else
                                                <button wire:click="toggleComplete({{ $activity->id }})"
                                                        class="btn btn-xs btn-ghost text-warning"
                                                        title="Reabrir">
                                                    <x-mary-icon name="o-arrow-path" class="h-3 w-3" />
                                                </button>
                                            @endif

                                            <button wire:click="editActivity({{ $activity->id }})"
                                                    class="btn btn-xs btn-ghost text-info"
                                                    title="Editar">
                                                <x-mary-icon name="o-pencil" class="h-3 w-3" />
                                            </button>

                                            <button
                                                    wire:click="deleteActivity({{ $activity->id }})"
                                                    onclick="return confirm('¿Eliminar esta actividad? Esta acción no se puede deshacer.')"
                                                    class="btn btn-xs btn-ghost text-error"
                                                    title="Eliminar"
                                            >
                                                <x-mary-icon name="o-trash" class="h-3 w-3" />
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Información de tiempo --}}
                                    <div class="space-y-1 mt-2 text-xs">
                                        @if($activity->status === 'overdue')
                                            @php
                                                $today = now();
                                                $diff = $activity->scheduled_at->diff($today);

                                                if ($diff->days > 0) {
                                                    $overdueText = "Vencida hace {$diff->days} día" . ($diff->days > 1 ? 's' : '');
                                                } elseif ($diff->h > 0) {
                                                    $overdueText = "Vencida hace {$diff->h} hora" . ($diff->h > 1 ? 's' : '');
                                                } elseif ($diff->i > 0) {
                                                    $overdueText = "Vencida hace {$diff->i} minuto" . ($diff->i > 1 ? 's' : '');
                                                } else {
                                                    $overdueText = "Vencida recientemente";
                                                }
                                            @endphp

                                            <div class="flex items-center gap-1 text-error">
                                                <x-mary-icon name="o-clock" class="h-3 w-3" />
                                                <span class="font-semibold">{{ $overdueText }}</span>
                                            </div>
                                        @endif

                                        @if($activity->scheduled_at)
                                            <div class="flex items-center gap-1 justify-between text-gray-800 dark:text-gray-400">
                                                <div class="flex items-center gap-1">
                                                    <x-mary-icon name="o-calendar" class="h-3 w-3" />
                                                    <span>Agendada:</span>
                                                </div>
                                                <span class="font-mono">{{ $activity->scheduled_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                        @endif

                                        @if($activity->date_action && $activity->status !== 'completed')
                                            <div class="flex items-center gap-1 text-info">
                                                <x-mary-icon name="o-play-circle" class="h-3 w-3" />
                                                <span>Iniciada: {{ $activity->date_action->format('d/m/Y H:i') }}</span>
                                            </div>
                                        @endif

                                        @if($activity->status === 'completed' && $activity->completed_at)
                                            <div class="flex items-center gap-1 text-success">
                                                <x-mary-icon name="o-check-circle" class="h-3 w-3" />
                                                <span>Completada: {{ $activity->completed_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-6 text-gray-400 text-xs">
                                    <x-mary-icon :name="$column['icon']" class="h-8 w-8 mx-auto mb-2 opacity-30" />
                                    <p>Sin actividades</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Resumen rápido --}}
{{--            <div class="mt-4 pt-3 border-t flex flex-wrap gap-3 justify-between items-center text-xs">--}}
{{--                <div class="flex gap-3">--}}
{{--                    @php--}}
{{--                        $counts = [--}}
{{--                            'overdue' => $activities->filter(fn($a) => $a->status === 'overdue')->count(),--}}
{{--//                            'in_progress' => $activities->filter(fn($a) => $a->status === 'in_progress')->count(),--}}
{{--                            'scheduled' => $activities->filter(fn($a) => $a->status === 'scheduled')->count(),--}}
{{--                            'pending' => $activities->filter(fn($a) => $a->status === 'pending')->count(),--}}
{{--                            'completed' => $activities->filter(fn($a) => $a->status === 'completed')->count(),--}}
{{--                        ];--}}
{{--                    @endphp--}}
{{--                    <span class="flex items-center gap-1 text-error">--}}
{{--                        <x-mary-icon name="o-exclamation-triangle" class="h-3 w-3" /> Vencidas: {{ $counts['overdue'] }}--}}
{{--                    </span>--}}
{{--                    <span class="flex items-center gap-1 text-info">--}}
{{--                        <x-mary-icon name="o-play-circle" class="h-3 w-3" /> En proceso: {{ $counts['in_progress'] }}--}}
{{--                    </span>--}}
{{--                    <span class="flex items-center gap-1 text-warning">--}}
{{--                        <x-mary-icon name="o-calendar" class="h-3 w-3" /> Agendadas: {{ $counts['scheduled'] }}--}}
{{--                    </span>--}}
{{--                    <span class="flex items-center gap-1 text-secondary">--}}
{{--                        <x-mary-icon name="o-clock" class="h-3 w-3" /> Pendientes: {{ $counts['pending'] }}--}}
{{--                    </span>--}}
{{--                    <span class="flex items-center gap-1 text-success">--}}
{{--                        <x-mary-icon name="o-check-circle" class="h-3 w-3" /> Completadas: {{ $counts['completed'] }}--}}
{{--                    </span>--}}
{{--                </div>--}}
{{--                <div class="text-gray-500">--}}
{{--                    Total: {{ $activities->count() }} actividades--}}
{{--                </div>--}}
{{--            </div>--}}
        @else
            {{-- Mensaje cuando no hay actividades --}}
            <div class="text-center py-12">
                <x-mary-icon name="o-document-text" class="h-12 w-12 text-gray-300 mx-auto mb-3" />
                <p class="text-gray-500 text-sm">No hay actividades registradas</p>
                <x-mary-button label="Crear primera actividad" wire:click="createActivity" class="btn-sm btn-primary mt-3" />
            </div>
        @endif
    </x-mary-card>

    {{-- Modal para crear/editar actividad --}}
    <x-mary-modal wire:model="showModal" title="{{ $editing ? 'Editar actividad' : 'Nueva actividad' }}" separator>
        <div class="space-y-4">
            <x-mary-select
                    wire:model="type_action"
                    label="Tipo de actividad"
                    :options="[
                        ['id' => 'Llamada', 'name' => 'Llamada'],
                        ['id' => 'Reunión', 'name' => 'Reunión'],
                        ['id' => 'Correo', 'name' => 'Correo'],
                        ['id' => 'Propuesta', 'name' => 'Propuesta'],
                        ['id' => 'Seguimiento', 'name' => 'Seguimiento'],
                        ['id' => 'Cotización', 'name' => 'Cotización'],
                        ['id' => 'Visita', 'name' => 'Visita'],
                        ['id' => 'Demo', 'name' => 'Demo'],
                        ['id' => 'Negociación', 'name' => 'Negociación'],
                        ['id' => 'Cierre', 'name' => 'Cierre'],
                        ['id' => 'Otro', 'name' => 'Otro'],
                    ]"
                    placeholder="Selecciona un tipo"
                    required
            />

            <x-mary-datetime
                    wire:model="scheduled_at"
                    label="Agendar para"
                    hint="Opcional - Deja en blanco si es inmediata"
                    without-time=false
                    type="datetime-local"
            />
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancelar" wire:click="showModal = false" />
            <x-mary-button label="{{ $editing ? 'Actualizar' : 'Crear' }}"
                           wire:click="{{ $editing ? 'updateActivity' : 'saveActivity' }}"
                           class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>
</div>