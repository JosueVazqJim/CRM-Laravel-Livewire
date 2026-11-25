@props(['product' => null, 'productForm' => null, 'tagOptions' => [], 'tabErrors' => []])

@php
    $model = $productForm ?? $product;
    $propName = $productForm ? 'productForm' : 'product';

    // Normalizar specifications
    $specs = data_get($model, 'specifications', []);
    if ($specs instanceof \Illuminate\Support\Collection) {
        $specs = $specs->toArray();
    }
    $specs = is_array($specs) ? $specs : (array) $specs;

    // Normalizar tags
    $tags = data_get($model, 'tags', []);
    if ($tags instanceof \Illuminate\Support\Collection) {
        $tags = $tags->toArray();
    }
    $tags = is_array($tags) ? $tags : (array) $tags;
@endphp

<div>
    <x-slot:label>
        <div class="flex items-center gap-1 whitespace-nowrap">
            <x-mary-icon name="o-cog-6-tooth" class="w-4 h-4" />
            <span>Avanzado</span>
            @if($tabErrors['advanced'] ?? false)
                <x-mary-badge value="{{ $tabErrors['advanced'] }}" class="badge-error badge-sm"/>
            @endif
        </div>
    </x-slot:label>

    <div class="space-y-5 p-1">
        {{-- Tags --}}
        <div>
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <x-mary-choices
                        label="Etiquetas"
                        wire:model="productForm.tags"
                        :options="$tagOptions"
                        option-value="id"
                        option-label="name"
                        placeholder="Seleccionar etiquetas..."
                        clearable
                        class="select-primary"
                        multiple
                    />
                </div>
                <x-mary-button
                    icon="o-plus"
                    wire:click="$set('tagModal', true)"
                    class="btn-ghost btn-sm mb-1"
                    tooltip="Agregar nueva etiqueta"
                    type="button"
                />
            </div>
            @if(empty($tagOptions))
                <p class="text-xs text-warning mt-1 flex items-center gap-1">
                    <x-mary-icon name="o-exclamation-triangle" class="w-3 h-3"/>
                    No hay etiquetas registradas. Crea una con el botón +
                </p>
            @endif
        </div>

        {{-- Especificaciones --}}
        <div class="mt-8 border-t pt-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <x-mary-icon name="o-list-bullet" class="w-5 h-5 text-primary" />
                    <h3 class="text-lg font-semibold">Especificaciones</h3>
                </div>
                <x-mary-badge value="{{ count($specs) }}" class="badge-primary badge-outline" />
            </div>

            <div class="space-y-3">
                @foreach($specs as $index => $spec)
                    <div class="group relative" wire:key="spec-row-{{ $index }}">
                        <div class="p-4 shadow-xl bg-gradient-to-br from-base-100 to-base-200">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <x-mary-input
                                    label="Nombre"
                                    wire:model="{{ $propName }}.specifications.{{ $index }}.name"
                                    placeholder="Ej: Memoria RAM"
                                    class="input-primary w-full"
                                />

                                <div class="sm:col-span-2">
                                    <x-mary-input
                                        label="Valor"
                                        wire:model="{{ $propName }}.specifications.{{ $index }}.value"
                                        placeholder="Ej: 16GB DDR4"
                                        class="input-primary w-full"
                                    />
                                </div>
                            </div>

                            @if(count($specs) > 1)
                                <div class="mt-3 flex justify-end">
                                    <x-mary-button
                                        icon="o-trash"
                                        class="btn-ghost btn-sm text-error hover:bg-error/10 hover:text-error border border-transparent hover:border-error/20"
                                        wire:click="removeSpecification({{ $index }})"
                                        tooltip="Eliminar especificación"
                                    />
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex justify-center sm:justify-start">
                <x-mary-button
                    icon="o-plus-circle"
                    label="Agregar especificación"
                    class="btn-primary btn-sm gap-2 hover:gap-3 transition-all"
                    wire:click="addSpecification()"
                />
            </div>

            <p class="text-xs text-primary mt-3 flex items-center gap-1">
                <x-mary-icon name="o-information-circle" class="w-4 h-4" />
                Agrega características técnicas del producto (RAM, almacenamiento, etc.)
            </p>
        </div>
    </div>
</div>

