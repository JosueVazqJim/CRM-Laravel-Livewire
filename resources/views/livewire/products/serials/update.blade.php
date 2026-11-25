<div class="space-y-6">
    {{-- Header --}}
    <x-mary-header title="Editar Serial de Producto" subtitle="Actualiza los datos del número de serie." separator progress-indicator>
        <x-slot:actions>
            <x-mary-button label="Volver" icon="o-arrow-left" :link="route('products.serials.show', $serial)" class="btn-ghost" />

        </x-slot:actions>
    </x-mary-header>

    <div class="space-y-6">
        {{-- Tarjeta contenedora del formulario --}}
        <x-mary-card class="shadow-xl bg-base-200">
            <x-mary-form wire:submit="save" class="space-y-5" no-separator>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Número de Serie --}}
                    <div class="sm:col-span-2">
                        <x-mary-input
                            label="Número de Serie *"
                            wire:model="serialForm.serial_number"
                            required
                            class="input-primary"
                            placeholder="Ej: SN-2024-001"
                        />
                    </div>

                    {{-- Estado --}}
                    <x-mary-select
                        label="Estado"
                        wire:model.live="serialForm.status"
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
{{--                        wire:model="serialForm.flag_color"--}}
{{--                        type="color"--}}
{{--                        class="input-primary"--}}
{{--                    />--}}

{{--                    <x-mary-colorpicker wire:model="serialForm.flag_color" label="Color de Bandera" suffix="Hex code"--}}
{{--                                        class="input-primary" />--}}

                    {{-- Precio de Compra --}}
                    <x-mary-input
                        label="Precio de Compra"
                        wire:model="serialForm.purchase_price"
                        type="number"
                        step="0.01"
                        class="input-primary"
                        placeholder="0.00"
                    />

                    {{-- Fecha de Compra --}}
                    <x-mary-input
                        label="Fecha de Compra"
                        wire:model="serialForm.purchased_at"
                        type="date"
                        class="input-primary"
                    />

                    {{-- Fecha de Entrada a Stock --}}
                    <x-mary-input
                        label="Fecha de Entrada a Stock"
                        wire:model="serialForm.entered_stock_at"
                        type="date"
                        class="input-primary"
                    />

                    {{-- Campos de venta (solo si el estado es Vendido) --}}
                    @if($serialForm->status === 'sold')
                        <x-mary-choices-offline
                            label="Vendido por"
                            wire:model="serialForm.sold_by_user_id"
                            :options="$sellerOptions"
                            no-result-text="No se encontraron usuarios"
                            single
                            searchable
                            class="select-primary"
                        />

                        <x-mary-choices-offline
                            label="Cotización (folio)"
                            wire:model="serialForm.quote_version_id"
                            :options="$quoteVersionOptions"
                            no-result-text="No se encontraron cotizaciones"
                            single
                            searchable
                            clearable
                            class="select-primary"
                        />

                        <x-mary-input
                            label="Fecha de Venta"
                            wire:model="serialForm.sold_at"
                            type="datetime-local"
                            class="input-primary"
                        />

                        <x-mary-input
                            label="Fecha de Salida"
                            wire:model="serialForm.exited_at"
                            type="date"
                            class="input-primary"
                        />
                    @endif

                    {{-- Notas --}}
                    <div class="sm:col-span-2">
                        <x-mary-textarea
                            label="Notas"
                            wire:model="serialForm.notes"
                            placeholder="Observaciones adicionales..."
                            rows="3"
                            class="input-primary"
                        />
                    </div>
                </div>

                {{-- Acciones del formulario --}}
                <div class="flex flex-col sm:flex-row gap-3 justify-between items-center pt-6 border-t mt-6">
                    <x-mary-button label="Cancelar" icon="o-x-mark" :link="route('products.serials.show', $serial)" class="btn-ghost" />
                    <x-mary-button label="Actualizar Serial" icon="o-check" class="btn-primary" type="submit" spinner />
                </div>

            </x-mary-form>
        </x-mary-card>
    </div>
</div>

