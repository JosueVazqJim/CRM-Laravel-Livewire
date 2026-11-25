@props(['activities' => [], 'tabErrors' => []])

<x-slot:label>
    <div class="flex items-center gap-1 whitespace-nowrap">
        @if($tabErrors['activities'] ?? false)
            <x-mary-badge value="{{ $tabErrors['activities'] }}" class="badge-error badge-sm"/>
        @endif
    </div>
</x-slot:label>

<div class="space-y-6">
    {{-- Actividades dinámicas --}}
    <div class="space-y-4">
        @forelse($activities as $activityIndex => $activity)
            <div class="card bg-base-100 shadow-md border border-base-200">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="card-title text-lg flex items-center gap-2">
                            <x-mary-icon name="o-calendar" class="w-5 h-5" />
                            Actividad {{ $activityIndex + 1 }}
                        </h3>
                        @if(count($activities) > 1)
                            <x-mary-button
                                icon="o-trash"
                                wire:click="removeActivity({{ $activityIndex }})"
                                class="btn-sm btn-error"
                                label="Eliminar"
                            />
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Tipo de Acción --}}
                        <div>
                            <x-mary-select
                                label="Tipo de Acción"
                                icon="o-bolt"
                                wire:model="activities.{{ $activityIndex }}.type_action"
                                :options="[
                                    ['id' => 'Llamada', 'name' => 'Llamada'],
                                    ['id' => 'Email', 'name' => 'Email'],
                                    ['id' => 'Reunión', 'name' => 'Reunión'],
                                    ['id' => 'Seguimiento', 'name' => 'Seguimiento'],
                                    ['id' => 'Propuesta', 'name' => 'Propuesta'],
                                    ['id' => 'Demo', 'name' => 'Demo'],
                                    ['id' => 'Negociación', 'name' => 'Negociación'],
                                    ['id' => 'Cierre', 'name' => 'Cierre'],
                                    ['id' => 'Otro', 'name' => 'Otro'],
                                ]"
                                :error="$errors->first('activities.' . $activityIndex . '.type_action')"
                                class="input-primary"
                            />
                        </div>

                        {{-- Fecha y Hora Programada --}}
                        <div>
{{--                            <x-mary-datetime--}}
{{--                                label="Fecha y Hora Programada"--}}
{{--                                icon="o-calendar"--}}
{{--                                wire:model="activities.{{ $activityIndex }}.scheduled_at"--}}
{{--                                :error="$errors->first('activities.' . $activityIndex . '.scheduled_at')"--}}
{{--                                class="input-primary"--}}
{{--                            />--}}
                            <x-mary-datetime
                                label="Fecha y Hora Programada"
                                wire:model="activities.{{ $activityIndex }}.scheduled_at"
                                type="datetime-local"
                                :error="$errors->first('activities.' . $activityIndex . '.scheduled_at')"
                                class="input-primary"
                            />
                        </div>

                        {{-- Estado de Completado --}}
                        <div class="form-control flex justify-center pt-2">
                            <label class="cursor-pointer label gap-2">
                                <x-mary-checkbox
                                    wire:model="activities.{{ $activityIndex }}.is_completed"
                                />
                                <span class="label-text">Completada</span>
                            </label>
                            @error('activities.' . $activityIndex . '.is_completed')
                                <span class="text-error text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info">
                <x-mary-icon name="o-information-circle" class="w-5 h-5" />
                <span>No hay actividades creadas. Haz clic en "Añadir Actividad" para crear una.</span>
            </div>
        @endforelse
    </div>

    {{-- Botón para añadir nueva actividad --}}
    <div class="flex justify-center pt-4">
        <x-mary-button
            label="Añadir Actividad"
            icon="o-plus-circle"
            wire:click="addActivity"
            class="btn-primary"
        />
    </div>

    {{-- Indicador de errores en la pestaña --}}
    @if($tabErrors['activities'] ?? 0)
        <div class="alert alert-warning">
            <x-mary-icon name="o-exclamation-triangle" class="w-4 h-4" />
            <span>Hay {{ $tabErrors['activities'] }} campo(s) con error en esta sección.</span>
        </div>
    @endif
</div>

