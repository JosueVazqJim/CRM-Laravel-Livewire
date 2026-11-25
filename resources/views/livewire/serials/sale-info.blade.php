@php
    $isSold = $serial->status === \App\Models\Products\ProductSerial::STATUS_SOLD;
@endphp
<x-mary-card class="shadow-lg">
    <x-slot:title>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-mary-icon name="{{ $isSold ? 'o-shopping-cart' : 'o-lock-closed' }}" class="h-5 w-5" />
                <span>Información de Venta</span>
            </div>

            @if($isSold && !$editing)
                <x-mary-button
                        icon="o-pencil"
                        label="Editar"
                        wire:click="enableEditing"
                        class="btn-sm"
                />
            @endif
        </div>
    </x-slot:title>

    <div class="space-y-4">
        @if(!$isSold)
            {{-- Bloqueada hasta marcar el serial como vendido --}}
            <div class="flex flex-col items-center text-center gap-2 py-4 text-base-content/50">
                <x-mary-icon name="o-lock-closed" class="h-8 w-8" />
                <p class="text-sm">Esta información se podrá capturar cuando el serial se marque como "Vendido".</p>
            </div>
        @elseif($editing)
            {{-- Vendido por --}}
            <div>
                <x-mary-choices-offline
                        label="Vendido por"
                        wire:model="sold_by_user_id"
                        :options="$sellerOptions"
                        no-result-text="No se encontraron usuarios"
                        single
                        searchable
                        class="select-primary"
                />
            </div>

            {{-- Cotización (búsqueda por folio integrada en el choices) --}}
            <div>
                <x-mary-choices-offline
                        label="Cotización (folio)"
                        wire:model="quote_version_id"
                        :options="$quoteVersionOptions"
                        no-result-text="No se encontraron cotizaciones"
                        single
                        searchable
                        clearable
                        class="select-primary"
                />
            </div>

            {{-- Fecha y hora de venta --}}
            <div>
                <x-mary-datetime
                        type="datetime-local"
                        label="Fecha y hora de venta"
                        wire:model="sold_at"
                        icon="o-calendar"
                        class="input-primary"
                />
            </div>

            {{-- Fecha de salida --}}
            <div>
                <x-mary-datetime
                        label="Fecha de salida"
                        wire:model="exited_at"
                        icon="o-calendar"
                        class="input-primary"
                />
            </div>

            <div class="flex gap-2 justify-end">
                <x-mary-button
                        label="Cancelar"
                        wire:click="cancelEditing"
                        wire:loading.attr="disabled"
                        wire:target="cancelEditing,save"
                        class="btn-ghost"
                />

                <x-mary-button
                        label="Guardar"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="btn-primary"
                />
            </div>
        @else
            {{-- Vendido por --}}
            <div>
                <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Vendido por</label>
                <div class="font-medium">{{ $serial->soldBy?->full_name ?? 'No asignado' }}</div>
            </div>

            {{-- Cotización --}}
            <div>
                <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Cotización</label>
                <div class="font-medium">
                    @if($serial->quoteVersion)
                        <a href="{{ route('quotes.show', $serial->quoteVersion) }}" class="link link-primary" wire:navigate>
                            {{ $serial->quoteVersion->folio }}
                        </a>
                    @else
                        Sin cotización asociada
                    @endif
                </div>
            </div>

            {{-- Fecha de venta --}}
            <div>
                <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Fecha y hora de venta</label>
                <div class="font-medium">{{ $serial->sold_at?->format('d/m/Y H:i') ?? 'No disponible' }}</div>
            </div>
        @endif
    </div>
</x-mary-card>
