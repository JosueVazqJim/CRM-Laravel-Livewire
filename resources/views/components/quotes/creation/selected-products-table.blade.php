@props([
    'items'    => [],
    'concepts' => [],
    'quoteForm' => null,
    'lead' => null,
    'selectedCurrencies' => [],
    'itemsTotalNoDiscount' => 0,
    'itemsTotalDiscount' => 0,
    'subtotal' => 0,
    'tax' => 0,
    'total' => 0,
])
<x-quotes.creation.concept-modal />

<x-mary-card class="shadow-xl bg-base-200">
    <x-slot:title class="text-lg font-bold flex items-center gap-2">
        <x-mary-icon name="o-shopping-cart" class="w-5 h-5" />
        Productos Seleccionados
        <x-mary-badge value="{{ count($items) }} items" class="badge-primary badge-sm" />
    </x-slot:title>

    <x-mary-form wire:submit="save" class="space-y-5" no-separator>
        {{-- Sección de conceptos adicionales (siempre visible) --}}
        <x-mary-card class="mt-4">
            <x-slot:title>
                <div class="flex items-center gap-2">
                    <x-mary-icon name="o-document-text" class="w-4 h-4"/>
                    Conceptos adicionales
                    <x-mary-badge value="{{ count($concepts) }}" class="badge-primary badge-sm"/>
                </div>
            </x-slot:title>

            @if(count($concepts) > 0)
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th>Nombre / Descripción</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-right">Precio USD</th>
                        <th class="text-center">Descuento %</th>
                        <th class="text-right">Total</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($concepts as $index => $concept)
                        <tr>
                            <td>
                                <div class="font-semibold text-sm">{{ $concept['name'] }}</div>
                                @if($concept['description'])
                                    <div class="text-xs text-gray-400">{{ $concept['description'] }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                <input type="number"
                                       wire:change="updateConcept({{ $index }}, 'quantity', $event.target.value)"
                                       value="{{ $concept['quantity'] }}"
                                       min="1" step="1"
                                       class="input input-bordered input-sm w-20 text-center input-primary"/>
                            </td>
                            <td class="text-right">
                                <input type="number"
                                       wire:change="updateConcept({{ $index }}, 'unit_price', $event.target.value)"
                                       value="{{ number_format($concept['unit_price'], 2) }}"
                                       min="0" step="0.01"
                                       class="input input-bordered input-sm w-24 text-right input-primary"/>
                            </td>
                            <td class="text-center">
                                <input type="number"
                                       wire:change="updateConcept({{ $index }}, 'discount', $event.target.value)"
                                       value="{{ number_format($concept['discount'], 2) }}"
                                       min="0" max="100" step="0.01"
                                       class="input input-bordered input-sm w-20 text-center input-primary"/>
                            </td>
                            <td class="text-right font-semibold text-sm">
                                ${{ number_format($concept['unit_price'] * $concept['quantity'] * (1 - $concept['discount'] / 100), 2) }}
                            </td>
                            <td>
                                <button type="button"
                                        wire:click="removeConcept({{ $index }})"
                                        class="btn btn-xs btn-ghost text-error"
                                        onclick="confirm('¿Eliminar concepto?') || event.stopImmediatePropagation()">
                                    <x-mary-icon name="o-trash" class="w-4 h-4"/>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif

            <x-mary-button
                    label="Nuevo concepto"
                    wire:click="$set('conceptModal', true)"
                    class="btn-ghost btn-sm mt-2"
                    icon="o-plus-circle"
                    type="button"
            />
        </x-mary-card>

        @if(count($items) > 0)
            <div class="overflow-x-auto">
                <div class="flex justify-between text-xs">
                    <x-mary-alert icon="o-exclamation-triangle" class="alert-warning">
                        <strong>Todos los precios de los productos seleccionados han sido convertidos a la moneda base (USD)
                            según la tasa de cambio aplicada. Revisa los precios unitarios antes de guardar la cotización.</strong>
                    </x-mary-alert>
                </div>
                <table class="table table-sm">
                    <thead>
                    <tr class="text-sm uppercase">
                        <th>Producto</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-right">Precio Unitario</th>
                        <th class="text-right">Descuento</th>
                        <th class="text-right">Subtotal</th>
                        <th class="text-right">Subtotal con Descuento</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($items as $index => $item)
                        <tr class="hover:bg-base-300/50 transition-colors">
                            <td>
                                <div class="flex items-start gap-3">
                                    <div class="shrink-0 w-16 h-16 rounded border border-base-300 bg-white flex items-center justify-center overflow-hidden">
                                        <img src="{{ $item['product_image_url'] }}" alt="{{ $item['product_name'] }}" class="max-w-full max-h-full object-contain" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-sm line-clamp-2">{{ $item['product_name'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $item['product_sku'] }}</div>
                                        <div class="text-sm text-gray-400 mt-1">
                                            <span class="badge badge-sm">Original: {{ number_format($item['unit_price_original'], 2) }} {{ $item['currency_original'] }}</span>
                                            <span class="badge badge-sm badge-ghost">Tasa: {{ number_format($item['exchange_rate_used'], 4) }}</span>
                                        </div>
                                        @if($item['custom_price'])
                                            <div class="badge badge-warning badge-xs mt-1">Precio modificado</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <input type="number"
                                       wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                       value="{{ $item['quantity'] }}"
                                       min="1"
                                       step="1"
                                       class="input input-bordered input-s w-20 text-center input-primary" />
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <span class="text-xs font-semibold">$</span>
                                    <input type="number"
                                           wire:key="unit-price-{{ $index }}-{{ $item['unit_price_converted_custom'] }}"
                                           wire:change="updateCustomPrice({{ $index }}, $event.target.value)"
                                           value="{{ number_format($item['unit_price_converted_custom'], 2) }}"
                                           step="0.01"
                                           class="input input-bordered input-s input-primary w-24 text-right {{ $item['custom_price'] ? 'input-warning' : '' }}" />
                                    @if($item['custom_price'])
                                        <button wire:click="resetPrice({{ $index }})"
                                                class="btn btn-xs btn-ghost tooltip"
                                                title="Resetear precio original"
                                                type="button">
                                            <x-mary-icon name="o-arrow-path" class="w-3 h-3" />
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <span class="text-xs font-semibold">%</span>
                                    <input type="number"
                                           wire:change="updateDiscount({{ $index }}, $event.target.value)"
                                           value="{{ number_format($item['discount'], 2) }}"
                                           step="0.01"
                                           min="0"
                                           max="100"
                                           class="input input-bordered input-s input-primary w-20 text-right" />
                                </div>
                            </td>
                            <td class="text-right font-semibold text-sm">
                                ${{ number_format($item['quantity'] * $item['unit_price_converted_custom'], 2) }}
                            </td>
                            <td class="text-right font-semibold text-sm">
                                ${{ number_format($item['unit_price_converted_custom'] * $item['quantity'] * (1 - $item['discount'] / 100), 2) }}
                            </td>
                            <td class="text-center">
                                <button type="button"
                                        wire:click="removeItem({{ $index }})"
                                        class="btn btn-xs btn-ghost text-error"
                                        onclick="confirm('¿Eliminar producto?') || event.stopImmediatePropagation()">
                                    <x-mary-icon name="o-trash" class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        <x-quotes.creation.currency-visualization
                :selectedCurrencies="$selectedCurrencies"
                :itemsTotalNoDiscount="$itemsTotalNoDiscount"
                :itemsTotalDiscount="$itemsTotalDiscount"
                :quoteForm="$quoteForm"
                :subtotal="$subtotal"
                :tax="$tax"
                :total="$total"
        />


        <x-quotes.creation.quote-notes :quoteForm="$quoteForm" />

        @if(count($items) > 0 || count($concepts) > 0)
            <div class="flex justify-end gap-2 mt-6">
                <x-mary-button label="Cancelar" icon="o-x-mark" link="{{ route('leads.show', $lead) }}" class="btn-outline" />
                <x-mary-button label="Guardar Cotización" icon="o-check" wire:click="save" class="btn-primary" spinner="save" />
            </div>
        @else
            <div class="text-center py-12">
                <x-mary-icon name="o-shopping-cart" class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                <h3 class="text-lg font-semibold text-gray-600">No hay productos seleccionados</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Selecciona productos del catálogo de arriba</p>
            </div>
        @endif
    </x-mary-form>
</x-mary-card>