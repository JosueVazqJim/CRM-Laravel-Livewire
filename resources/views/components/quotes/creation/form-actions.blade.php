@props(['lead', 'defaultNotes' => []])

<x-mary-modal wire:model="myModal2" title="Nueva nota" subtitle="Registra información importante">
    <x-mary-form no-separator>
        @if(count($defaultNotes) > 0)
            <div class="mb-3">
                <p class="text-xs text-gray-500 mb-2">
                    Notas predeterminadas — clic para usar:
                </p>
                <div class="space-y-1 max-h-40 overflow-y-auto">
                    @foreach($defaultNotes as $note)
                        <button
                            type="button"
                            wire:click="$set('noteContent', {{ Js::from($note['content']) }})"
                            class="w-full text-left text-xs p-2 rounded border border-base-300
                                   hover:border-primary hover:bg-primary/5 transition-colors"
                        >
                            <span class="font-semibold block">{{ $note['title'] }}</span>
                            <span class="text-gray-400 line-clamp-1">{{ $note['content'] }}</span>
                        </button>
                    @endforeach
                </div>
                <div class="divider text-xs my-2">o escribe una nota personalizada</div>
            </div>
        @endif
        <x-mary-textarea label="Nota" wire:model="noteContent" placeholder="Escribe tu nota aquí ..."
                         hint="Máximo 5000 caracteres" rows="5" class="textarea-primary" />
        <x-slot:actions>
            <x-mary-button label="Cancelar" @click="$wire.myModal2 = false" />
            <x-mary-button label="Agregar nota" @click="$wire.addNote($wire.noteContent)" class="btn-primary" />
        </x-slot:actions>
    </x-mary-form>
</x-mary-modal>
