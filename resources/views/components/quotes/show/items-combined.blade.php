@props(['quoteForm', 'concepts' => []])

@php
    $hasItems = count($quoteForm->items) > 0;
    $hasConcepts = count($concepts) > 0;

    $productTotal = collect($quoteForm->items)->sum(fn($i) =>
        $i['unit_price_converted_custom']
        * $i['quantity']
        * (1 - $i['discount'] / 100)
    );

    $conceptTotal = collect($concepts)->sum(fn($c) =>
        $c['unit_price']
        * $c['quantity']
        * (1 - $c['discount'] / 100)
    );
@endphp

<div class="space-y-6">

    {{-- ===================== PRODUCTOS ===================== --}}
    <x-mary-card class="shadow-sm border border-base-300">

        <x-slot:title>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">
                        Productos del catálogo
                    </h2>

                    <p class="text-sm text-gray-500">
                        Productos agregados a la cotización
                    </p>
                </div>

                <x-mary-badge
                        value="{{ count($quoteForm->items) }}"
                        class="badge-primary badge-lg"
                />
            </div>
        </x-slot:title>

        @if($hasItems)

            <div class="overflow-x-auto rounded-xl border border-base-300">

                <table class="table">
                    <thead class="bg-base-200">
                    <tr class="text-xs uppercase tracking-wide text-gray-500">
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio unitario</th>
                        <th>Descuento</th>
                        <th class="text-right">Total</th>
                    </tr>
                    </thead>

                    <tbody>

                    @foreach($quoteForm->items as $item)

                        <tr class="hover:bg-base-200/40 transition">

                            {{-- PRODUCTO --}}
                            <td class="min-w-[320px]">
                                <div class="flex gap-4 items-center">

                                    <div class="w-14 h-14 shrink-0 rounded-xl border border-base-300 bg-white flex items-center justify-center overflow-hidden">
                                        <img
                                                src="{{ $item['product_image_url'] }}"
                                                alt="{{ $item['product_name'] }}"
                                                class="max-w-full max-h-full object-contain"
                                        />
                                    </div>

                                    <div class="space-y-1">

                                        <div class="font-semibold leading-tight">
                                            {{ $item['product_name'] }}
                                        </div>

                                        <div class="flex items-center gap-2 flex-wrap">

                                            <span class="font-mono text-xs text-gray-500">
                                                {{ $item['product_sku'] }}
                                            </span>

                                            @if($item['custom_price'])
                                                <span class="badge badge-warning badge-sm">
                                                    Precio ajustado
                                                </span>
                                            @endif

                                            @if($item['discount'] > 0)
                                                <span class="badge badge-error badge-sm">
                                                    -{{ $item['discount'] }}%
                                                </span>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- CANTIDAD --}}
                            <td>
                                <div class="font-semibold">
                                    {{ number_format($item['quantity']) }}
                                </div>
                            </td>

                            {{-- PRECIO --}}
                            <td>
                                <div class="space-y-1">

                                    <div class="font-semibold">
                                        {{ $this->formatCurrency($item['unit_price_converted_custom']) }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        Original:
                                        {{ number_format($item['unit_price_original'], 2) }}
                                        {{ $item['currency_original'] }}
                                    </div>

                                    <div class="text-xs text-gray-400">
                                        Tasa de Cambio:
                                        {{ number_format($item['exchange_rate_used'], 4) }}
                                    </div>

                                </div>
                            </td>

                            {{-- DESCUENTO --}}
                            <td>
                                @if($item['discount'] > 0)
                                    <span class="font-semibold text-error">
                                        {{ $item['discount'] }}%
                                    </span>
                                @else
                                    <span class="text-gray-400">
                                        —
                                    </span>
                                @endif
                            </td>

                            {{-- TOTAL --}}
                            <td class="text-right">

                                <div class="font-bold text-base">
                                    {{ $this->formatCurrency($item['total']) }}
                                </div>

                                <div class="text-xs text-gray-400">
                                    subtotal:
                                    {{ $this->formatCurrency($item['subtotal']) }}
                                </div>

                            </td>

                        </tr>

                    @endforeach

                    </tbody>
                </table>
            </div>

            {{-- FOOTER --}}
            <div class="mt-6 flex justify-end">

                <div class="w-full max-w-sm rounded-2xl bg-base-200 p-5 border border-base-300">

                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-500">Productos</span>

                        <span class="font-mono font-semibold">
                            {{ $this->formatCurrency($productTotal) }} USD
                        </span>
                    </div>

                    @if($hasConcepts)
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-500">Conceptos</span>

                            <span class="font-mono font-semibold">
                                $ {{ number_format($conceptTotal, 2) }} USD
                            </span>
                        </div>
                    @endif

                    <div class="divider my-2"></div>

                    <div class="flex justify-between items-center">
                        <span class="font-bold text-base">
                            Total
                        </span>

                        <span class="font-bold text-xl text-primary font-mono">
                            $ {{ number_format($productTotal + $conceptTotal, 2) }} USD
                        </span>
                    </div>

                </div>

            </div>

        @else

            <div class="py-14 flex flex-col items-center justify-center text-center">

                <x-mary-icon
                        name="o-cube-transparent"
                        class="w-14 h-14 text-gray-300"
                />

                <h3 class="mt-4 font-semibold text-base">
                    No hay productos
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Esta cotización todavía no contiene productos.
                </p>

            </div>

        @endif

    </x-mary-card>

    {{-- ===================== CONCEPTOS ===================== --}}
    @if($hasConcepts)

        <x-mary-card class="shadow-sm border border-base-300">

            <x-slot:title>
                <div class="flex items-center justify-between">

                    <div>
                        <h2 class="text-lg font-semibold">
                            Conceptos adicionales
                        </h2>

                        <p class="text-sm text-gray-500">
                            Servicios, instalaciones o cargos extra
                        </p>
                    </div>

                    <x-mary-badge
                            value="{{ count($concepts) }}"
                            class="badge-primary badge-lg"
                    />

                </div>
            </x-slot:title>

            <div class="space-y-3">

                @foreach($concepts as $concept)

                    <div class="rounded-2xl border border-base-300 p-4 hover:bg-base-200/40 transition">

                        <div class="flex items-start justify-between gap-4">

                            <div class="space-y-1">

                                <h3 class="font-semibold">
                                    {{ $concept['name'] }}
                                </h3>

                                @if($concept['description'])
                                    <p class="text-sm text-gray-500">
                                        {{ $concept['description'] }}
                                    </p>
                                @endif

                                <div class="flex items-center gap-3 text-xs text-gray-400">

                                    <span>
                                        Cantidad:
                                        {{ number_format($concept['quantity']) }}
                                    </span>

                                    <span>
                                        Descuento:
                                        {{ number_format($concept['discount'], 2) }}%
                                    </span>

                                </div>

                            </div>

                            <div class="text-right">

                                <div class="text-sm text-gray-400">
                                    $ {{ number_format($concept['unit_price'], 2) }} USD
                                </div>

                                <div class="font-bold text-lg">
                                    $
                                    {{ number_format(
                                        $concept['unit_price']
                                        * $concept['quantity']
                                        * (1 - $concept['discount'] / 100),
                                    2) }}
                                    USD
                                </div>

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        </x-mary-card>

    @endif

</div>