<x-mary-collapse separator>
    <x-slot:heading>
        <x-mary-icon name="o-pencil-square" class="w-5 h-5" />
        Notas de la Cotización
        <x-mary-badge value="{{ count($quoteForm->notes) }}" class="badge-primary badge-sm" />
    </x-slot:heading>
    <x-slot:content>
        <x-mary-button label="Agregar" @click="$wire.myModal2 = true" icon="o-plus" class="btn-sm btn-primary my-2" />
        @forelse($quoteForm->notes as $index => $note)
            <div class="group bg-base-100 rounded-lg p-4 border-l-4 border-primary shadow-sm hover:shadow-md transition-all duration-200 my-3">
                <div class="flex">
                    <div class="flex-1">
                        <p class="text-gray-700 dark:text-gray-300 text-lg">{{ $note['note'] }}</p>
                    </div>
                    <button type="button" wire:click="removeNote({{ $index }})"
                            onclick="confirm('¿Eliminar esta nota?') || event.stopImmediatePropagation()"
                            class="group-hover:opacity-100 btn btn-xs text-error ml-2">
                        <x-mary-icon name="o-trash" class="w-4 h-4" />
                    </button>
                </div>
            </div>
        @empty
            <div class="text-center py-8">
                <x-mary-icon name="o-document-text" class="w-12 h-12 mx-auto text-gray-400 mb-2" />
                <p class="text-sm text-gray-500">No hay notas registradas</p>
                <p class="text-xs text-gray-400 mt-1">Haz clic en "Agregar" para crear tu primera nota</p>
            </div>
        @endforelse
    </x-slot:content>
</x-mary-collapse>