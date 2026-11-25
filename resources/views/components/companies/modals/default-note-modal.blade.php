@props(['editingNoteIndex' => null])

<x-mary-modal wire:model="defaultNoteModal"
    :title="$editingNoteIndex !== null ? 'Editar nota' : 'Nueva nota predeterminada'">
    <div class="space-y-3">
        <x-mary-input
            wire:model="defaultNoteForm.title"
            label="Título de la nota"
            placeholder="Ej: Condiciones de pago, Garantía, Entrega..."
            hint="Máximo 100 caracteres"
        />
        <x-mary-textarea
            wire:model="defaultNoteForm.content"
            label="Contenido"
            placeholder="Escribe el texto de la nota..."
            rows="5"
            hint="Máximo 5000 caracteres"
            class="textarea-primary"
        />
    </div>
    <x-slot:actions>
        <x-mary-button
            label="Cancelar"
            wire:click="$set('defaultNoteModal', false)"
            type="button"
        />
        <x-mary-button
            label="{{ $editingNoteIndex !== null ? 'Guardar cambios' : 'Agregar nota' }}"
            wire:click="saveDefaultNote"
            class="btn-primary"
            spinner="saveDefaultNote"
            type="button"
        />
    </x-slot:actions>
</x-mary-modal>
