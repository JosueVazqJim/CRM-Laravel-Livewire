@props(['product' => null, 'productForm' => null, 'tabErrors' => []])

@php
    $model = $productForm ?? $product;
    $propName = $productForm ? 'productForm' : 'product';
@endphp

<div>
    <x-slot:label>
        <div class="flex items-center gap-1 whitespace-nowrap">
            <x-mary-icon name="o-currency-dollar" class="w-4 h-4" />
            <span>Precios de venta</span>
            @if($tabErrors['pricing'] ?? false)
                <x-mary-badge value="{{ $tabErrors['pricing'] }}" class="badge-error badge-sm"/>
            @endif
        </div>
    </x-slot:label>

    <div class="space-y-5 p-1">
        <x-mary-select
                label="Moneda"
                wire:model="{{ $propName }}.currency"
                :options="[
                    ['id' => 'MXN', 'name' => 'Peso Mexicano (MXN)'],
                    ['id' => 'USD', 'name' => 'Dólar Estadounidense (USD)'],
//                    ['id' => 'EUR', 'name' => 'Euro (EUR)'],
                ]"
                option-key="id"
                option-value="id"
                option-label="name"
                class="select-primary"
        />
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-mary-input
                label="Precio Regular *"
                wire:model="{{ $propName }}.price_regular"
                type="number"
                step="0.01"
                required
                class="input-primary"
                placeholder="0.00"
            />

            <x-mary-input
                label="Precio Mínimo"
                wire:model="{{ $propName }}.price_min"
                type="number"
                step="0.01"
                class="input-primary"
                placeholder="0.00"
            />

            <x-mary-input
                label="Precio Máximo"
                wire:model="{{ $propName }}.price_max"
                type="number"
                step="0.01"
                class="input-primary"
                placeholder="0.00"
            />

            <x-mary-input
                label="Costo de Instalación"
                wire:model="{{ $propName }}.installation_cost"
                type="number"
                step="0.01"
                class="input-primary"
                placeholder="0.00"
            />
        </div>

        <div class="mt-6 p-4 bg-base-300 rounded-lg">
            <h3 class="font-semibold mb-3 flex items-center gap-2">
                <x-mary-icon name="o-information-circle" class="w-5 h-5" />
                Información de Precios
            </h3>
            <ul class="text-sm space-y-2">
                <li><strong>Precio Regular:</strong> Precio de venta estándar</li>
                <li><strong>Precio Mínimo:</strong> Precio más bajo permitido</li>
                <li><strong>Precio Máximo:</strong> Precio más alto permitido</li>
                <li><strong>Costo Instalación:</strong> Costo adicional de instalación o servicio</li>
            </ul>
        </div>
    </div>
</div>

