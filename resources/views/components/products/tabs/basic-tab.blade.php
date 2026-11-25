@props(['product' => null, 'productForm' => null, 'tabErrors' => [], 'typeOptions' => [], 'categoryOptions' => [], 'companyOptions' => [], 'isEdit' => false])

@php
    $model = $productForm ?? $product;
    $propName = $productForm ? 'productForm' : 'product';
@endphp

<div>
    <x-slot:label>
        <div class="flex items-center gap-1 whitespace-nowrap">
            <x-mary-icon name="o-cube" class="w-4 h-4" />
            <span>Información Básica</span>
            @if($tabErrors['basic'] ?? false)
                <x-mary-badge value="{{ $tabErrors['basic'] }}" class="badge-error badge-sm"/>
            @endif
        </div>
    </x-slot:label>

    <div class="space-y-5 p-1">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <x-mary-input
                    label="Nombre del Producto"
                    wire:model="{{ $propName }}.name"
                    required
                    class="input-primary"
                    placeholder="Ej: Laptop Dell XPS 13"
                />
            </div>

            <x-mary-input
                label="SKU"
                wire:model="{{ $propName }}.sku"
                required
                class="input-primary"
                placeholder="Ej: DELL-XPS-13-001"
            />

            <div>
                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <x-mary-select
                            label="Tipo de Producto"
                            wire:model="{{ $propName }}.product_type_id"
                            required
                            :options="$typeOptions"
                            option-key="id"
                            option-value="id"
                            option-label="name"
                            class="select-primary"
                            placeholder="Seleccione una opción"
                        />
                    </div>
                    <x-mary-button
                        icon="o-plus"
                        wire:click="$set('typeModal', true)"
                        class="btn-ghost btn-sm mb-1"
                        tooltip="Agregar nuevo tipo"
                        type="button"
                    />
                </div>
                @if(empty($typeOptions))
                    <p class="text-xs text-warning mt-1 flex items-center gap-1">
                        <x-mary-icon name="o-exclamation-triangle" class="w-3 h-3"/>
                        No hay tipos registrados. Crea uno con el botón +
                    </p>
                @endif
            </div>

            <div>
                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <x-mary-select
                            label="Categoría"
                            wire:model="{{ $propName }}.product_category_id"
                            required
                            :options="$categoryOptions"
                            option-key="id"
                            option-value="id"
                            option-label="name"
                            class="select-primary"
                            placeholder="Seleccione una categoría"
                        />
                    </div>
                    <x-mary-button
                        icon="o-plus"
                        wire:click="$set('categoryModal', true)"
                        class="btn-ghost btn-sm mb-1"
                        tooltip="Agregar nueva categoría"
                        type="button"
                    />
                </div>
                @if(empty($categoryOptions))
                    <p class="text-xs text-warning mt-1 flex items-center gap-1">
                        <x-mary-icon name="o-exclamation-triangle" class="w-3 h-3"/>
                        No hay categorías registradas. Crea una con el botón +
                    </p>
                @endif
            </div>

            <x-mary-choices label="Corporativo" class="select-primary" wire:model="{{ $propName }}.company_id" :options="$companyOptions"
                            single
                            clearable
                            required
                            placeholder="Seleccione una empresa del corporativo"
            />

            <x-mary-colorpicker wire:model="{{ $propName }}.color" label="Color" suffix="Hex code" class="select-primary" />
        </div>
    </div>
</div>
