@props([
    'quoteForm',
    'currencySummary' => []
])

<div class="card bg-base-200 shadow-sm sticky top-8">
    <div class="card-body">
        <h2 class="card-title">Resumen de Totales</h2>

        {{-- Resumen en USD (por defecto) --}}
        <div class="space-y-2 mb-4">
            <div class="flex justify-between">
                <span>Subtotal productos:</span>
                <span class="font-mono">{{ $this->formatCurrency($quoteForm->subtotal - ($quoteForm->shipping_price ?? 0)) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Envío:</span>
                <span class="font-mono">{{ $this->formatCurrency($quoteForm->shipping_price ?? 0) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Subtotal + Envío:</span>
                <span class="font-mono">{{ $this->formatCurrency($quoteForm->subtotal) }}</span>
            </div>
            <div class="flex justify-between">
                <span>IVA (16%):</span>
                <span class="font-mono">{{ $this->formatCurrency($quoteForm->tax) }}</span>
            </div>
            <div class="border-t pt-2 mt-2">
                <div class="flex justify-between text-lg font-bold">
                    <span>TOTAL:</span>
                    <span class="text-primary dark:text-gray-400">{{ $this->formatCurrency($quoteForm->total) }}</span>
                </div>
            </div>
        </div>

        {{-- Totales convertidos a otras monedas --}}
        @if(count($currencySummary) > 1)
            <div class="divider text-xs text-gray-500 my-2">
                Totales por moneda
            </div>
            <div class="space-y-3 text-sm">
                @foreach($currencySummary as $summary)
                    @if($summary['currency'] !== 'USD')
                        <div class="bg-base-100 p-3 rounded-lg">
                            <div class="font-semibold mb-2 flex items-center justify-between">
                                <span>{{ $summary['currency'] }}</span>
                                <span class="text-xs badge badge-outline">
                                    1 USD = {{ number_format($summary['exchange_rate'], 4) }}
                                </span>
                            </div>
                            <div class="space-y-1 text-xs">
                                <div class="flex justify-between">
                                    <span>Subtotal productos:</span>
                                    <span class="font-mono">
                                        {{ number_format($summary['subtotal_products'], 2) }} {{ $summary['currency'] }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Envío:</span>
                                    <span class="font-mono">
                                        {{ number_format($summary['shipping_price'], 2) }} {{ $summary['currency'] }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Subtotal + Envío:</span>
                                    <span class="font-mono">
                                        {{ number_format($summary['subtotal'], 2) }} {{ $summary['currency'] }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span>IVA (16%):</span>
                                    <span class="font-mono">
                                        {{ number_format($summary['tax'], 2) }} {{ $summary['currency'] }}
                                    </span>
                                </div>
                                <div class="flex justify-between font-bold border-t pt-1">
                                    <span>TOTAL:</span>
                                    <span class="font-mono text-primary">
                                        {{ number_format($summary['total'], 2) }} {{ $summary['currency'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        <div class="divider text-xs text-gray-500 my-2">
            Tasas aplicadas al momento
        </div>
        <div class="text-xs space-y-1">
            @foreach($quoteForm->exchangeRatesSnapshot as $rate)
                <div class="flex justify-between">
                    <span>1 USD → {{ $rate['to_currency'] }}</span>
                    <span class="font-mono">{{ number_format($rate['rate'], 4) }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>