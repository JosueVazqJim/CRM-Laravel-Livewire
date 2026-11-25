<x-mary-card class="shadow-xl">
    <x-slot:title>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-mary-icon name="o-document-text" class="h-5 w-5" />
                <span>Mis Notas</span>
                @if(!$editing && count($notes) > 0)
                    <span class="badge badge-primary badge-sm">{{ count($notes) }}</span>
                @endif
            </div>

            @if(!$editing)
                <x-mary-button
                        icon="o-pencil"
                        label="Editar"
                        wire:click="enableEditing"
                        class="btn-sm"
                />
            @endif
        </div>
    </x-slot:title>

    <div class="">
        @if($editing)
            {{-- Notas dinámicas --}}
            <div class="space-y-4">
                @forelse($notes as $noteIndex => $note)
                    <div class="card bg-base-100 shadow-md border border-base-200">
                        <div class="card-body">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="card-title text-lg flex items-center gap-2">
                                    <x-mary-icon name="o-document-text" class="w-5 h-5" />
                                    Nota {{ $noteIndex + 1 }}
                                </h3>
                                <x-mary-button
                                        icon="o-trash"
                                        wire:click="removeNote({{ $noteIndex }})"
                                        class="btn-sm btn-error"
                                        label="Eliminar"
                                />
                            </div>

                            {{-- Contenido de la Nota --}}
                            <div>
                                <x-mary-textarea
                                        label="Contenido"
                                        wire:model="notes.{{ $noteIndex }}.content"
                                        placeholder="Escribe tu nota aquí..."
                                        :error="$errors->first('notes.' . $noteIndex . '.content')"
                                        class="textarea-primary"
                                        rows="3"
                                        required
                                />
                            </div>

                            {{-- Fecha de creación (solo lectura si ya existe) --}}
                            @if(!empty($note['id']))
                                <div class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                    <x-mary-icon name="o-clock" class="w-4 h-4" />
                                    Creada: {{ \Carbon\Carbon::parse($note['created_at'])->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info">
                        <x-mary-icon name="o-information-circle" class="w-5 h-5" />
                        <span>No hay notas creadas. Haz clic en "Añadir Nota" para crear una.</span>
                    </div>
                @endforelse
            </div>

            {{-- Botón para añadir nueva nota --}}
            <div class="flex justify-center pt-4">
                <x-mary-button
                        label="Añadir Nota"
                        icon="o-plus-circle"
                        wire:click="addNote"
                        class="btn-primary"
                />
            </div>

            <div class="flex gap-2 justify-end pt-4">
                <x-mary-button
                        label="Cancelar"
                        wire:click="cancelEditing"
                        wire:loading.attr="disabled"
                        wire:target="cancelEditing"
                        class="btn-ghost"
                />

                <x-mary-button
                        label="Guardar"
                        wire:click="update"
                        wire:loading.attr="disabled"
                        wire:target="update"
                        class="btn-primary"
                />
            </div>
        @else
            @if(count($notes) > 0)
                <div class="space-y-3">
                    @foreach($notes as $note)
                        <div class="border-l-4 border-primary pl-4 py-2">
                            <p class="text-gray-800 dark:text-gray-300 whitespace-pre-line">{{ $note['content'] }}</p>

                            @if(!empty($note['created_at']))
                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                    <x-mary-icon name="o-clock" class="w-4 h-4" />
                                    {{ \Carbon\Carbon::parse($note['created_at'])->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info">
                    <x-mary-icon name="o-information-circle" class="w-5 h-5" />
                    <span>No tienes notas registradas para este lead.</span>
                </div>
            @endif
        @endif
    </div>
</x-mary-card>