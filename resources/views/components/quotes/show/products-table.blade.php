@props([
    'quoteForm',
    'headers' => [
        ['key' => 'product', 'label' => 'Producto'],
        ['key' => 'sku', 'label' => 'SKU'],
        ['key' => 'quantity', 'label' => 'Cantidad', 'class' => 'text-center'],
        ['key' => 'unit_price_original', 'label' => 'Precio Original', 'class' => 'text-right'],
        ['key' => 'exchange_rate_used', 'label' => 'Tasa de Cambio', 'class' => 'text-right'],
        ['key' => 'unit_price_converted', 'label' => 'Precio Convertido', 'class' => 'text-right'],
        ['key' => 'unit_price_converted_custom', 'label' => 'Precio Ajustado', 'class' => 'text-right'],
        ['key' => 'discount', 'label' => 'Descuento', 'class' => 'text-center'],
        ['key' => 'subtotal', 'label' => 'Subtotal', 'class' => 'text-right'],
        ['key' => 'total', 'label' => 'Total', 'class' => 'text-right']
    ]
])

<div class="card bg-base-100 shadow-sm">
    <div class="card-body">
        <h2 class="card-title">Productos Cotizados</h2>
        <div class="overflow-x-auto">
            <x-mary-table :headers="$headers" :rows="$quoteForm->items">
                @scope('cell_product', $item)
                <div class="flex items-center gap-2">
                    <img src="{{ $item['product_image_url'] }}"
                         class="w-10 h-10 object-cover rounded"
                         alt="{{ $item['product_name'] }}"/>
                    <div>
                        <p class="font-medium">{{ $item['product_name'] }}</p>
                        @if($item['custom_price'])
                            <span class="text-xs badge badge-warning">Precio modificado</span>
                        @endif
                    </div>
                </div>
                @endscope

                @scope('cell_sku', $item)
                <span class="text-sm font-mono">{{ $item['product_sku'] }}</span>
                @endscope

                @scope('cell_quantity', $item)
                {{ number_format($item['quantity'], 0) }}
                @endscope

                @scope('cell_unit_price_original', $item)
                {{ number_format($item['unit_price_original'], 2) }}
                <span class="text-xs text-gray-500">{{ $item['currency_original'] }}</span>
                @endscope

                @scope('cell_exchange_rate_used', $item)
                1 USD = {{ number_format($item['exchange_rate_used'], 4) }}
                @endscope

                @scope('cell_unit_price_converted', $item)
                {{ $this->formatCurrency($item['unit_price_converted']) }}
                @endscope

                @scope('cell_unit_price_converted_custom', $item)
                {{ $this->formatCurrency($item['unit_price_converted_custom']) }}
                @endscope

                @scope('cell_discount', $item)
                @if($item['discount'] > 0)
                    <span class="badge badge-error badge-sm">{{ $item['discount'] }}% off</span>
                @else
                    <span class="text-gray-400">0% off</span>
                @endif
                @endscope

                @scope('cell_subtotal', $item)
                {{ $this->formatCurrency($item['subtotal']) }}
                @endscope

                @scope('cell_total', $item)
                {{ $this->formatCurrency($item['total']) }}
                @endscope

                <x-slot:footer class="bg-base-200 text-right">
                    <tr>
                        <td colspan="10" class="text-right font-bold">
                            Total de productos:
                            {{ $this->formatCurrency($quoteForm->subtotal - ($quoteForm->shipping_price ?? 0)) }}
                        </td>
                    </tr>
                </x-slot:footer>
            </x-mary-table>
        </div>
    </div>
</div>