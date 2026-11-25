<div class="space-y-6">
    {{-- Header --}}
    <x-mary-header title="Agregar Serial de Producto" subtitle="Ingresa un nuevo número de serie al inventario." separator progress-indicator>
        <x-slot:actions>
            <x-mary-button label="Volver" icon="o-arrow-left" :link="route('products.serials.index', $product)" class="btn-ghost" />
        </x-slot:actions>
    </x-mary-header>

    <div class="space-y-6">
        {{-- Tarjeta contenedora del formulario --}}
        <x-mary-card class="shadow-xl bg-base-200">
            <x-mary-form wire:submit="save" class="space-y-5" no-separator>

                {{-- Switch Individual / Por lote --}}
                <x-mary-toggle
                    label="Crear en lote"
                    hint="Activa esta opción para registrar varios números de serie a la vez, compartiendo estado, precio y fechas."
                    wire:model.live="serial.isBatchMode"
                    right
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Número de Serie (modo individual) --}}
                    @unless($serial->isBatchMode)
                        <div class="sm:col-span-2">
                            <x-mary-input
                                label="Número de Serie *"
                                wire:model="serial.serial_number"
                                required
                                class="input-primary"
                                placeholder="Ej: SN-2024-001"
                            />
                        </div>
                    @endunless

                    {{-- Estado --}}
                    <x-mary-select
                        label="Estado"
                        wire:model.live="serial.status"
                        required
                        :options="[
                            ['id' => 'in_stock', 'name' => 'En Stock'],
                            ['id' => 'sold', 'name' => 'Vendido'],
                            ['id' => 'damaged', 'name' => 'Dañado'],
                            ['id' => 'defective', 'name' => 'Defectuoso'],
                        ]"
                        option-key="id"
                        option-value="id"
                        option-label="name"
                        class="select-primary"
                    />

                    {{-- Color de Bandera --}}
{{--                    <x-mary-input--}}
{{--                        label="Color de Bandera"--}}
{{--                        wire:model="serial.flag_color"--}}
{{--                        type="color"--}}
{{--                        class="input-primary"--}}
{{--                    />--}}

{{--                    <x-mary-colorpicker wire:model="serial.flag_color" label="Color de Bandera" suffix="Hex code"--}}
{{--                                        class="input-primary" />--}}


                    {{-- Precio de Compra --}}
                    <x-mary-input
                        label="Precio de Compra"
                        wire:model="serial.purchase_price"
                        type="number"
                        step="0.01"
                        class="input-primary"
                        placeholder="0.00"
                    />

                    {{-- Fecha de Compra --}}
                    <x-mary-input
                        label="Fecha de Compra"
                        wire:model="serial.purchased_at"
                        type="date"
                        required
                        class="input-primary"
                    />

                    {{-- Fecha de Entrada a Stock --}}
                    <x-mary-input
                        label="Fecha de Entrada a Stock"
                        wire:model="serial.entered_stock_at"
                        type="date"
                        required
                        class="input-primary"
                    />

                    {{-- Campos de venta (solo si el estado es Vendido) --}}
                    @if($serial->status === 'sold')
                        <x-mary-choices-offline
                            label="Vendido por"
                            wire:model="serial.sold_by_user_id"
                            :options="$sellerOptions"
                            no-result-text="No se encontraron usuarios"
                            single
                            searchable
                            class="select-primary"
                        />

                        <x-mary-choices-offline
                            label="Cotización (folio)"
                            wire:model="serial.quote_version_id"
                            :options="$quoteVersionOptions"
                            no-result-text="No se encontraron cotizaciones"
                            single
                            searchable
                            clearable
                            class="select-primary"
                        />

                        <x-mary-input
                            label="Fecha de Venta"
                            wire:model="serial.sold_at"
                            type="datetime-local"
                            class="input-primary"
                        />

                        <x-mary-input
                            label="Fecha de Salida"
                            wire:model="serial.exited_at"
                            type="date"
                            class="input-primary"
                        />
                    @endif

                    {{-- Notas (modo individual) --}}
                    @unless($serial->isBatchMode)
                        <div class="sm:col-span-2">
                            <x-mary-textarea
                                label="Notas"
                                wire:model="serial.notes"
                                placeholder="Observaciones adicionales..."
                                rows="3"
                                class="input-primary"
                            />
                        </div>
                    @endunless
                </div>

                {{-- Filas del lote --}}
                @if($serial->isBatchMode)
                    <div class="space-y-3" x-data x-on:batch-row-added.window="$nextTick(() => $refs.lastSerialInput?.focus())">
                        <h3 class="font-semibold text-sm">Seriales del lote ({{ count($serial->batchItems) }})</h3>

                        @error('serial.batchItems')
                            <div class="text-error text-sm">{{ $message }}</div>
                        @enderror

                        @foreach($serial->batchItems as $index => $item)
                            <div wire:key="batch-row-{{ $item['key'] }}" class="flex flex-col sm:flex-row gap-3 items-start border border-base-300 rounded-lg p-3">
                                <div class="flex-1 w-full">
                                    <x-mary-input
                                        label="Número de Serie #{{ $index + 1 }} *"
                                        wire:model="serial.batchItems.{{ $index }}.serial_number"
                                        wire:keydown.enter.prevent="addBatchRow"
                                        x-ref="lastSerialInput"
                                        class="input-primary"
                                        placeholder="Ej: SN-2024-{{ str_pad($index + 1, 3, '0', STR_PAD_LEFT) }}"
                                    />
                                </div>
                                <div class="flex-1 w-full">
                                    <x-mary-input
                                        label="Notas"
                                        wire:model="serial.batchItems.{{ $index }}.notes"
                                        class="input-primary"
                                        placeholder="Observaciones (opcional)"
                                    />
                                </div>
                                <x-mary-button
                                    icon="o-trash"
                                    wire:click="removeBatchRow('{{ $item['key'] }}')"
                                    type="button"
                                    class="btn-ghost btn-circle text-error mt-6"
                                    :disabled="count($serial->batchItems) <= 1"
                                />
                            </div>
                        @endforeach

                        <x-mary-button label="+ Agregar otro serial" wire:click="addBatchRow" type="button" class="btn-sm btn-outline btn-primary" />
                    </div>
                @endif

                {{-- Acciones del formulario --}}
                <div class="flex flex-col sm:flex-row gap-3 justify-between items-center pt-6 border-t mt-6">
                    <x-mary-button label="Cancelar" icon="o-x-mark" :link="route('products.serials.index', $product)" class="btn-ghost" />
                    <x-mary-button
                        :label="$serial->isBatchMode ? 'Crear Seriales' : 'Crear Serial'"
                        icon="o-check"
                        class="btn-primary"
                        type="submit"
                        spinner="save"
                    />
                </div>

            </x-mary-form>
        </x-mary-card>
    </div>
</div>

