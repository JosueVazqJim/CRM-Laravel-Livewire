<x-mary-card class="shadow-lg">
    <x-slot:title class="flex items-center gap-2">
        <x-mary-icon name="o-clock" />
        Estado del Serial
    </x-slot:title>

    <div class="space-y-4">
        <div class="flex items-center gap-3">
            <x-mary-badge
                :value="$this->statusLabels()[$serial->status] ?? ucfirst(str_replace('_', ' ', $serial->status))"
                :class="'badge ' . ($this->statusColors()[$serial->status] ?? 'badge-base')"
            />
        </div>

        @if($this->statusActions()[$serial->status] ?? null)
            <div class="divider my-2"></div>

            <p class="text-sm text-base-content/60 mb-2">Cambiar a:</p>

            <div class="flex flex-col gap-2">
                @foreach($this->statusActions()[$serial->status] as $action)
                    <x-mary-button
                        label="{{ $action['label'] }}"
                        icon="{{ $action['icon'] }}"
                        wire:click="transitionTo('{{ $action['status'] }}')"
                        class="btn-outline btn-sm"
                    />
                @endforeach
            </div>
        @endif

        @can('seriales.eliminar')
            <div class="divider my-2"></div>

            <x-mary-button
                label="Eliminar Serial"
                icon="o-trash"
                onclick="document.getElementById('delete-serial-modal-{{ $serial->id }}').showModal()"
                class="btn-error btn-outline btn-sm w-full"
            />
        @endcan
    </div>

    @can('seriales.eliminar')
        {{-- Modal nativo (id + showModal) para no depender del entangle de wire:model --}}
        <x-mary-modal id="delete-serial-modal-{{ $serial->id }}" title="Eliminar serial" subtitle="Serial {{ $serial->serial_number }}">
            <x-mary-alert
                title="Esta acción no se puede deshacer"
                description="El serial y su historial de movimientos se eliminarán permanentemente de la base de datos."
                icon="o-exclamation-triangle"
                class="alert-error"
            />

            <x-slot:actions>
                <x-mary-button label="Cancelar" onclick="document.getElementById('delete-serial-modal-{{ $serial->id }}').close()" class="btn-ghost" />
                <x-mary-button label="Eliminar" icon="o-trash" wire:click="deleteSerial" spinner="deleteSerial" class="btn-error" />
            </x-slot:actions>
        </x-mary-modal>
    @endcan
</x-mary-card>

