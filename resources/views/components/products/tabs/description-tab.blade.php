@props(['product' => null, 'productForm' => null, 'tabErrors' => []])

@php
    $model = $productForm ?? $product;
    $propName = $productForm ? 'productForm' : 'product';

    // Manejo de imagen
    $image = data_get($model, 'image_path', null);
    if (empty($image)) {
        $imageSrc = asset('images/no-image.jpg');
    } elseif (filter_var($image, FILTER_VALIDATE_URL) || str_starts_with($image, 'http')) {
        $imageSrc = $image;
    } else {

        if (str_starts_with($image, 'images/')) {
            $imageSrc = asset($image);
        } else {
            $imageSrc = asset('storage/' . $image);
        }
    }
@endphp

<div>
    <x-slot:label>
        <div class="flex items-center gap-1 whitespace-nowrap">
            <x-mary-icon name="o-document-text" class="w-4 h-4" />
            <span>Descripción</span>
            @if($tabErrors['description'] ?? false)
                <x-mary-badge value="{{ $tabErrors['description'] }}" class="badge-error badge-sm"/>
            @endif
        </div>
    </x-slot:label>

    <div class="space-y-5 p-1">
        <div class="grid grid-cols-1 gap-4">
            {{-- Imagen del Producto --}}
            <div class="w-full">
                <x-mary-file
                    label=" "
                    wire:model="{{ $propName }}.image_path"
                    hint=" "
                    accept="image/png, image/jpeg"
                    change-text="Cambiar"
                    crop-text="Recortar"
                    crop-title-text="Ajusta la imagen del producto"
                    crop-cancel-text="Cancelar"
                    crop-save-text="Guardar"
                    crop-after-change
                    class="w-full flex justify-center"
                >
                    <div class="flex flex-col items-center w-full">
                        <span class="text-sm font-medium mb-2 text-center w-full">Imagen del Producto</span>
                        <div class="flex justify-center w-full mb-2">
                            <img src="{{ $imageSrc }}" alt="Imagen del producto" class="h-60 w-80 object-cover border-4 border-base-200 rounded-lg" />
                        </div>
                        <span class="text-xs text-gray-500 text-center w-full">Max 10MB, formatos: jpg, png</span>
                    </div>
                </x-mary-file>
            </div>

            {{-- Descripción Corta --}}
            <x-mary-textarea
                label="Descripción Breve"
                wire:model="{{ $propName }}.short_description"
                placeholder="Una línea breve sobre el producto..."
                hint="Max 5000 caracteres"
                rows="3"
                class="input-primary"
            />

            {{-- Descripción Larga --}}
            <x-mary-textarea
                label="Descripción Detallada"
                wire:model="{{ $propName }}.long_description"
                placeholder="Descripción completa, características, beneficios..."
                hint="Max 50000 caracteres"
                rows="6"
                class="input-primary"
            />
        </div>
    </div>
</div>

