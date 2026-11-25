@props(['defaultNotes' => [], 'tabErrors' => []])

<div>
    <x-slot:label>
        <div class="flex items-center gap-1 whitespace-nowrap">
            <x-mary-icon name="o-document-text" class="w-4 h-4"/>
            <span>Notas predeterminadas</span>
            @if(count($defaultNotes) > 0)
                <x-mary-badge value="{{ count($defaultNotes) }}"
                    class="badge-primary badge-sm"/>
            @endif
        </div>
    </x-slot:label>

    <div class="space-y-4 p-1">

        {{-- Botón agregar --}}
        <div class="flex justify-between items-center">
            <p class="text-sm text-gray-500">
                Estas notas estarán disponibles al crear cotizaciones
                para esta empresa.
            </p>
            <x-mary-button
                icon="o-plus"
                label="Nueva nota"
                wire:click="openNewDefaultNote"
                class="btn-primary btn-sm"
                type="button"
            />
        </div>

        {{-- Listado de notas --}}
        @if(count($defaultNotes) > 0)
            <div class="space-y-2">
                @foreach($defaultNotes as $index => $note)
                    <div class="card bg-base-100 shadow-sm border border-base-300"
                         wire:key="default-note-{{ $index }}">
                        <div class="card-body p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-sm truncate">
                                        {{ $note['title'] }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                                        {{ $note['content'] }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    {{-- Reordenar --}}
                                    <x-mary-button
                                        icon="o-chevron-up"
                                        wire:click="moveDefaultNoteUp({{ $index }})"
                                        class="btn-ghost btn-xs"
                                        type="button"
                                        :disabled="$index === 0"
                                    />
                                    <x-mary-button
                                        icon="o-chevron-down"
                                        wire:click="moveDefaultNoteDown({{ $index }})"
                                        class="btn-ghost btn-xs"
                                        type="button"
                                        :disabled="$index === count($defaultNotes) - 1"
                                    />
                                    {{-- Editar --}}
                                    <x-mary-button
                                        icon="o-pencil"
                                        wire:click="openEditDefaultNote({{ $index }})"
                                        class="btn-ghost btn-xs"
                                        type="button"
                                    />
                                    {{-- Eliminar --}}
                                    <x-mary-button
                                        icon="o-trash"
                                        wire:click="removeDefaultNote({{ $index }})"
                                        class="btn-ghost btn-xs text-error"
                                        type="button"
                                        wire:confirm="¿Eliminar esta nota predeterminada?"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <x-mary-icon name="o-document-text"
                    class="w-12 h-12 mx-auto text-gray-400 mb-3"/>
                <p class="text-sm text-gray-500">
                    No hay notas predeterminadas para esta empresa.
                </p>
            </div>
        @endif

    </div>
</div>
