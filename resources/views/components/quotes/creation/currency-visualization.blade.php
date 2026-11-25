<div class="flex items-center justify-between mb-4 mx-6 justify-end">
    <h3 class="text-lg font-bold flex items-center gap-2">
        <x-mary-icon name="o-currency-dollar" class="w-5 h-5" />
        Visualización por Moneda
    </h3>
</div>

<div class="flex flex-wrap gap-3 mx-6 justify-end">
    @foreach($this->currencyCalculations as $code => $calculation)
        @if($code === 'USD') @continue @endif
        <label class="cursor-pointer">
            <input type="checkbox" wire:model.live="selectedCurrencies" value="{{ $code }}" class="hidden peer" />
            <div class="px-4 py-2 rounded-full font-medium transition-all duration-200
                        bg-base-100 border-2 peer-checked:bg-primary peer-checked:text-primary-content
                        peer-checked:border-primary hover:scale-105 border-base-300 text-base-content">
                {{ $calculation['currency'] }}
                <span class="text-xs ml-1 opacity-75">({{ number_format($calculation['rate'], 2) }})</span>
            </div>
        </label>
    @endforeach
</div>

<div dir="rtl" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 my-3">
    {{-- USD Card --}}
    <div dir="ltr" class="card bg-gradient-to-br from-base-100 to-base-200 shadow-lg hover:shadow-xl transition-all duration-300">
        <div class="card-body p-4">
            <div class="flex justify-between items-start mb-3">
                <div><div class="text-2xl font-black">USD</div></div>
                <div class="badge badge-primary badge-lg">Tasa: 1 USD = 1 USD</div>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between border-b pb-1"><span>Total productos sin descuento:</span><span class="font-semibold">${{ number_format($itemsTotalNoDiscount, 2) }}</span></div>
                <div class="flex justify-between border-b pb-1"><span>Total con descuento:</span><span class="font-semibold">${{ number_format($itemsTotalDiscount, 2) }}</span></div>
                <div class="flex justify-between border-b pb-1"><span>Costo de envío:</span><span class="font-semibold">${{ number_format($quoteForm->shipping_price, 2) }}</span></div>
                <div class="flex justify-between border-b pb-1"><span>Subtotal:</span><span class="font-semibold">${{ number_format($subtotal, 2) }}</span></div>
                <div class="flex justify-between border-b pb-1"><span>IVA (16%):</span><span class="font-semibold text-warning">${{ number_format($tax, 2) }}</span></div>
            </div>
            <div class="mt-3 pt-3 border-t-2 border-primary">
                <div class="flex justify-between"><span class="text-lg font-bold">Total a pagar:</span><span class="text-2xl font-black">${{ number_format($total, 2) }}</span></div>
            </div>
        </div>
    </div>

    {{-- Otras monedas seleccionadas --}}
    @foreach($selectedCurrencies as $currencyCode)
        @php $calc = $this->currencyCalculations[$currencyCode]; @endphp
        <div dir="ltr" class="card bg-gradient-to-br from-base-100 to-base-200 shadow-lg hover:shadow-xl transition-all duration-300">
            <div class="card-body p-4">
                <div class="flex justify-between items-start mb-3">
                    <div><div class="text-2xl font-black">{{ $calc['currency'] }}</div></div>
                    <div class="badge badge-primary badge-lg">Tasa: 1 USD = {{ number_format($calc['rate'], 4) }} {{ $calc['currency'] }}</div>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between border-b pb-1"><span>Total productos sin descuento:</span><span class="font-semibold">${{ number_format($calc['items_total_no_discount'], 2) }}</span></div>
                    <div class="flex justify-between border-b pb-1"><span>Total con descuento:</span><span class="font-semibold">${{ number_format($calc['items_total_discount'], 2) }}</span></div>
                    <div class="flex justify-between border-b pb-1"><span>Costo de envío:</span><span class="font-semibold">${{ number_format($calc['shipping'], 2) }}</span></div>
                    <div class="flex justify-between border-b pb-1"><span>Subtotal:</span><span class="font-semibold">${{ number_format($calc['subtotal'], 2) }}</span></div>
                    <div class="flex justify-between border-b pb-1"><span>IVA (16%):</span><span class="font-semibold text-warning">${{ number_format($calc['tax'], 2) }}</span></div>
                </div>
                <div class="mt-3 pt-3 border-t-2 border-primary">
                    <div class="flex justify-between"><span class="text-lg font-bold">Total a pagar:</span><span class="text-2xl font-black">${{ number_format($calc['total'], 2) }}</span></div>
                </div>
            </div>
        </div>
    @endforeach
</div>