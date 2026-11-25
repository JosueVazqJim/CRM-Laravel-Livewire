<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización {{ $quoteVersion->folio }}</title>
    <style>
        @php
            $company   = $quoteVersion->quote->company;
            $themeRgb  = $company->theme_rgb ?? '74,163,97';
            // Si el theme viene como hex, convertirlo a RGB
            // Se asume que theme ya es "r,g,b" o "#rrggbb"
        @endphp
        :root{
            --theme: {{ $themeRgb }};
            --theme-color: rgb({{ $themeRgb }});
            --theme-light: rgba({{ $themeRgb }}, 0.10);
            --theme-mid:   rgba({{ $themeRgb }}, 0.20);
        }

        @page {
            margin: 10mm 8mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #222222;
            margin: 0;
            padding: 0;
        }

        /* ───── HEADER ───── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .header-logo-cell {
            width: 30%;
            vertical-align: middle;
        }
        .header-address-cell {
            width: 35%;
            vertical-align: top;
            font-size: 13px;
            color: #444;
            padding-left: 10px;
        }
        .header-vendor-cell {
            width: 35%;
            vertical-align: top;
            font-size: 13px;
            color: #444;
        }

        /* ───── COTIZACIÓN INFO ───── */
        .quote-box {
            background-color: var(--theme-light);
            border-radius: 15px;
            padding: 10px 14px;
            margin-bottom: 16px;
        }
        .quote-info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .quote-left {
            width: 50%;
            vertical-align: top;
        }
        .quote-right {
            width: 50%;
            vertical-align: top;
        }
        .quote-title {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 4px;
            color: #555;
        }
        .quote-folio {
            font-size: 13px;
            font-weight: bold;
            color: #555;
        }
        .quote-date {
            font-size: 13px;
            color: #555;
            margin-top: 2px;
        }
        .quote-total {
            font-size: 13px;
            color: #555;
            margin-top: 2px;
        }
        /* ───── NOTAS (debajo del recuadro) ───── */
        .notes-section {
            margin-bottom: 16px;
        }
        .notes-title {
            font-size: 12px;
            font-weight: bold;
            color: #222;
            border-bottom: 2px solid #555;
            padding-bottom: 3px;
            margin-bottom: 6px;
        }
        .notes-table {
            width: 100%;
            border-collapse: collapse;
        }
        .notes-table td {
            font-size: 10px;
            color: #555;
            padding: 3px 6px;
            vertical-align: top;
            border-bottom: 1px dotted #555;
        }
        .notes-table td.note-num {
            width: 20px;
            color: #555;
            font-weight: bold;
            text-align: center;
        }
        .client-label {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 4px;
            color: #555;
        }
        .client-row {
            font-size: 13px;
            color: #555;
            margin-top: 2px;
        }

        /* ───── TABLA DE PRODUCTOS ───── */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .products-table thead tr {
        }
        .products-table th {
            font-size: 11px;
            text-transform: uppercase;
            color: #555;
            padding: 6px 8px;
            border-bottom: 2px solid var(--theme-color);
            text-align: left;
            vertical-align: bottom;
        }
        .products-table td {
            padding: 8px;
            border-bottom: 1px solid var(--theme-color);
            font-size: 11px;
            vertical-align: top;
        }
        .product-img-cell {
            width: 7%;
            text-align: center;
        }
        .product-img-placeholder {
            width: 40px;
            height: 40px;
            background-color: #ddd;
            display: inline-block;
            font-size: 8px;
            text-align: center;
            line-height: 40px;
            color: #888;
        }
        .product-name {
            font-weight: bold;
            color: black;
            font-size: 11px;
        }
        .product-sku {
            color: #777;
            font-size: 11px;
            margin-top: 2px;
        }
        .col-qty    { width: 7%;  text-align: center; }
        .col-price  { width: 12%; text-align: right; white-space: nowrap; }
        .col-disc   { width: 7%;  text-align: center; }
        .col-total  { width: 12%; text-align: right; font-weight: bold; white-space: nowrap; }
        .col-subtotal { width: 12%; text-align: right; font-weight: bold; white-space: nowrap; }
        .col-prod   { width: 17%; }
        .col-desc   { width: 26%; }

        /* ───── TOTALES ───── */
        .totals-section-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .totals-usd-cell {
            width: 33%;
            vertical-align: top;
            padding-right: 10px;
        }
        .totals-mxn-cell {
            width: 33%;
            vertical-align: top;
            padding-right: 10px;
        }
        .totals-firma-cell {
            width: 34%;
            vertical-align: top;
            text-align: center;
        }
        .totals-box {
            padding: 8px 10px;
        }
        .totals-box-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 6px;
            color: #333;
        }
        .totals-flag {
            font-size: 12px;
            vertical-align: middle;
        }
        .totals-inner-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-inner-table td {
            font-size: 11px;
            padding: 2px 0;
            color: #444;
        }
        .totals-inner-table td.amount {
            text-align: right;
        }
        .totals-grand-row td {
            font-weight: bold;
            font-size: 11px;
            color: #222;
            background: #d5d5d5;
            padding: 5px;
        }
        .firma-name {
            font-size: 11px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }
        .payment-logos-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .payment-logo-cell {
            width: 50%;
            text-align: center;
            padding: 4px;
        }
        .payment-logo-badge {
            display: inline-block;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 9px;
            font-weight: bold;
            color: #333;
            min-width: 50px;
            text-align: center;
        }

        /* ───── TÉRMINOS ───── */
        .terms-box {
            border-top: 2px solid ;
            padding-top: 8px;
            font-size: 9px;
            color: #555;
        }
        .terms-title {
            font-weight: bold;
            color: #222;
            font-size: 11px;
            margin-bottom: 4px;
        }

        /* ───── SEPARADOR ───── */
        .divider {
            border: none;
            border-top: 1px solid #e0e0e0;
            margin: 10px 0;
        }

        /* ─── FOOTER ────────────────────────────────────────── */
        .footer {
            border-top: 1px solid var(--border);
            margin-top: 24px;
            padding-top: 8px;
            font-size: 9px;
            color: #aaa;
            text-align: center;
        }
    </style>
</head>
<body>

@php
    $clientName    = $clientName    ?? null;
    $clientCompany = $clientCompany ?? null;
    $clientPhone   = $clientPhone   ?? null;
    $clientEmail   = $clientEmail   ?? null;
    $clientAddress = $clientAddress ?? null;

    $company        = $quoteVersion->quote->company;
    $lead           = $quoteVersion->quote->lead;
    $firstContact   = $lead->contactPeople->first();
    $contactChannels = $firstContact?->channels ?? collect();

    $resolvedName    = $clientName    ?? ($firstContact?->name_contact ?? $lead->name_company);
    $resolvedCompany = $clientCompany ?? $lead->name_company;
    $resolvedPhone   = $clientPhone   ?? ($contactChannels->first(fn($c) => in_array(strtolower($c->type), ['phone', 'telefono', 'teléfono']))?->channel);
    $resolvedEmail   = $clientEmail   ?? ($contactChannels->first(fn($c) => strtolower($c->type) === 'email')?->channel);

    $billingAddress = $lead->billingInfo?->address;

    $resolvedAddress = $clientAddress ?? null;

    if (!$resolvedAddress && $billingAddress) {
        $parts = array_filter([
            trim(($billingAddress->street ?? '') . ' ' .
                 ($billingAddress->exterior_number ?? '') .
                 ($billingAddress->interior_number
                     ? ' Int. ' . $billingAddress->interior_number
                     : '')),
            $billingAddress->colony ?? null,
            $billingAddress->municipality ?? null,
            $billingAddress->state ?? null,
            $billingAddress->postal_code
                ? 'C.P. ' . $billingAddress->postal_code
                : null,
        ]);
        $resolvedAddress = implode(', ', $parts) ?: null;
    }

    // ── Modo de moneda: 'both' | 'usd' | 'mxn' ──
    $summaryCol = collect($currencySummary ?? []);
    $summaryUsd = $summaryCol->firstWhere('currency', 'USD');
    $summaryMxn = $summaryCol->firstWhere('currency', 'MXN');
    $hasMxn     = $summaryMxn !== null;

    $currencyDisplay = $currencyDisplay ?? 'both';
    if (!in_array($currencyDisplay, ['both', 'usd', 'mxn'], true)) {
        $currencyDisplay = 'both';
    }

    $showUsdBox  = $currencyDisplay !== 'mxn' || !$hasMxn;
    $showMxnBox  = $hasMxn && $currencyDisplay !== 'usd';
    $bothVisible = $showUsdBox && $showMxnBox;
    $boxWidth    = $bothVisible ? '33%' : '66%';

    // Cuando el modo es "solo MXN", los renglones de productos y conceptos se
    // muestran convertidos con la tasa del snapshot en vez de en USD.
    $mxnOnly       = $showMxnBox && !$showUsdBox;
    $itemsRate     = $mxnOnly ? (float) $summaryMxn['exchange_rate'] : 1.0;
    $itemsCurrency = $mxnOnly ? 'MXN' : 'USD';
@endphp

        <!-- ════════════════════════════════════════
     HEADER: Logo + Dirección + Vendedor
════════════════════════════════════════ -->
<table class="header-table">
    <tr>
        <td class="header-logo-cell" style="vertical-align: middle;">
            @php
                $logoPath = public_path(
                    'storage/logos/' . basename($company->avatar_url)
                );
            @endphp

            @if($isPreview)
                <img src="{{ asset('storage/logos/' . basename($company->avatar_url)) }}"
                     style="display:block; width:190px; height:auto;">
            @else
                <img src="{{ $logoPath }}"
                     style="display:block; width:190px; height:auto;">
            @endif
        </td>
        <td class="header-address-cell" style="vertical-align: top;">
            {{--            1.ª privada de la 17 Sur 2310-A,<br>--}}
            {{--            Los Volcanes, 72410 Puebla, Pue.<br>--}}
            {{ $company->companyAddress->street}} {{ $company->companyAddress->exterior_number}} - {{ $company->companyAddress->interior_number}} <br>
            {{ $company->companyAddress->colony}}, {{ $company->companyAddress->postal_code}} <br>
            {{ $company->companyAddress->stateRelation->name}}, {{ $company->companyAddress->municipalityRelation->name}}<br>
            <b>Tel. Oficina: </b>{{ $company->companyPhones->pluck('phone')->implode(', ') }}
        </td>
        <td class="header-vendor-cell" style="vertical-align: top;">
            <b>Vendedor: </b>{{$quoteVersion->quote->user->name}}<br>
            <strong>Tel: </strong>{{ $quoteVersion->quote->user->phones->pluck('phone')->implode(', ') }}
            <br>
            <strong>Correo: </strong>{{$quoteVersion->quote->user->email}}<BR>
        </td>
    </tr>
</table>

<!-- ════════════════════════════════════════
     BLOQUE COTIZACIÓN + CLIENTE
════════════════════════════════════════ -->
<div class="quote-box">
    <table class="quote-info-table">
        <tr>
            <td class="quote-left">
                <div class="quote-title">COTIZACIÓN</div>
                <div class="quote-folio">FOLIO: {{ $quoteVersion->folio }}</div>
                <div class="quote-date">{{ \Carbon\Carbon::parse($quoteVersion->created_at)->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</div>
                <div class="quote-total"><b>Total:</b> $ {{ number_format($mxnOnly ? (float) $summaryMxn['total'] : (float) $quoteVersion->total, 2) }} {{ $itemsCurrency }}</div>
            </td>
            <td class="quote-right">
                {{--                TODO revisar extracion de datos debido a que hay varias opciones para nombre, --}}
                {{--                telefono, correo, etc. dependiendo de la información que tenga el lead y el contacto asociado. --}}
                {{--                Se recomienda estandarizar esto para evitar confusiones.--}}
                <div class="client-label">DATOS DEL CLIENTE</div>
                @if($showClientName ?? true)
                    <div class="client-row" id="client-name-row">
                        <b>Nombre:</b> <span id="client-name-value">{{ $resolvedName }}</span>
                    </div>
                @endif
                @if($showClientCompany ?? true)
                    <div class="client-row" id="client-company-row">
                        <b>Empresa:</b> <span id="client-company-value">{{ $resolvedCompany }}</span>
                    </div>
                @endif
                @if($showClientPhone ?? true)
                    <div class="client-row" id="client-phone-row">
                        <b>Teléfono:</b> <span id="client-phone-value">{{ $resolvedPhone ?? '—' }}</span>
                    </div>
                @endif
                @if($showClientEmail ?? true)
                    <div class="client-row" id="client-email-row">
                        <b>Correo:</b> <span id="client-email-value">{{ $resolvedEmail ?? '—' }}</span>
                    </div>
                @endif
                @if($showClientAddress ?? true)
                    <div class="client-row" id="client-address-row">
                        <b>Dirección:</b> <span id="client-address-value">{{ $resolvedAddress ?? '—' }}</span>
                    </div>
                @endif
            </td>
        </tr>
    </table>
</div>

<!-- ════════════════════════════════════════
     TABLA DE PRODUCTOS
════════════════════════════════════════ -->
<table class="products-table">
    <thead>
    <tr>
        <th style="width:7%;">IMAGEN</th>
        <th class="col-prod">PRODUCTO</th>
        <th class="col-desc">DESCRIPCIÓN</th>
        <th class="col-qty">CANTIDAD</th>
        <th class="col-price">PRECIO<br>UNITARIO</th>
        <th class="col-disc">DESC.</th>
        <th class="col-total">PRECIO<br>UNITARIO CON<br>DESCUENTO</th>
        <th class="col-subtotal">SUBTOTAL</th>
    </tr>
    </thead>
    <tbody>
    <!-- Producto 1 -->
    @foreach($quoteVersion->items as $item)
        <tr>
            <td class="product-img-cell">
                @php
                    $imgPath = $item->product->image_path
                        ? public_path('storage/' . $item->product->image_path)
                        : null;
                    $imgExists = $imgPath && file_exists($imgPath);
                @endphp

                @if($imgExists)
                    <table style="width:40px; height:40px;" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="width:40px; height:40px; background-color:#fff; border:1px solid #ddd; text-align:center; vertical-align:middle;">
                                @if($isPreview)
                                    <img src="{{ asset('storage/' . $item->product->image_path) }}"
                                         style="max-width:38px; max-height:38px;">
                                @else
                                    <img src="{{ $imgPath }}"
                                         style="max-width:38px; max-height:38px;">
                                @endif
                            </td>
                        </tr>
                    </table>
                @else
                    <div class="product-img-placeholder">IMG</div>
                @endif
            </td>
            <td class="col-prod">
                <div class="product-name">{{ $item->product->name }}</div>
                <div class="product-sku"><b>SKU: {{ $item->product->sku }}</b></div>
                <div class="product-sku">Color: {{ $item->product->color }}</div>
            </td>
            <td class="col-desc">
                {!! $item->product->formatted_description !!}
            </td>
            <td class="col-qty">{{ number_format((float) $item->quantity, 0) }}</td>
            <td class="col-price">$ {{ number_format((float) $item->unit_price_converted * $itemsRate, 2) }}</td>
            <td class="col-disc">{{ number_format((float) $item->discount, 0) }} %</td>
            <td class="col-total">$ {{ number_format($item->unitPriceConvertedDiscount() * $itemsRate, 2) }}</td>
            <td class="col-subtotal">$ {{ number_format($item->lineTotalWithDiscount() * $itemsRate, 2) }}</td>
        </tr>
    @endforeach


    </tbody>
</table>

<!-- ════════════════════════════════════════
     CONCEPTOS ADICIONALES
════════════════════════════════════════ -->
@if(isset($concepts) && $concepts->isNotEmpty())
    <table class="products-table" style="margin-top: 8px;">
        <thead>
        <tr>
            <th colspan="2">CONCEPTOS ADICIONALES</th>
            <th class="col-qty">CANTIDAD</th>
            <th class="col-price">PRECIO {{ $itemsCurrency }}</th>
            <th class="col-disc">DESCUENTO</th>
            <th class="col-total">TOTAL</th>
        </tr>
        </thead>
        <tbody>
        @foreach($concepts as $concept)
            <tr>
                <td colspan="2">
                    <div class="product-name">{{ $concept->name }}</div>
                    @if($concept->description)
                        <div class="product-sku">{{ $concept->description }}</div>
                    @endif
                </td>
                <td class="col-qty">{{ number_format((float)$concept->quantity, 0) }}</td>
                <td class="col-price">$ {{ number_format((float)$concept->unit_price * $itemsRate, 2) }}</td>
                <td class="col-disc">{{ number_format((float)$concept->discount, 0) }} %</td>
                <td class="col-total">
                    $ {{ number_format($concept->lineTotalWithDiscount() * $itemsRate, 2) }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

<!-- ════════════════════════════════════════
     MERCADO PAGO — OPCIONES MSI
════════════════════════════════════════ -->
@php
    $summaryMpCol = collect($currencySummary ?? []);
    $summaryMpMxn = $summaryMpCol->firstWhere('currency', 'MXN');
    $totalMxn     = $summaryMpMxn ? (float) $summaryMpMxn['total'] : null;

    $comisionBase = 0.0349;
    $fijo         = 4.64;
    $plazos       = [3 => 0.0469, 6 => 0.0769, 9 => 0.1119, 12 => 0.1289];

    $opcionesMp = [];
    if ($totalMxn) {
        foreach ($plazos as $meses => $comMsi) {
            $subtotalSinIva = ($totalMxn * ($comisionBase + $comMsi)) + $fijo;
            $totalConIva    = $subtotalSinIva * 1.16;
            $totalConMp     = $totalMxn + $totalConIva;
            $pagoMensual    = $totalConMp / $meses;
            $opcionesMp[$meses] = [
                'meses'        => $meses,
                'comision_pct' => round(($comisionBase + $comMsi) * 100, 2),
                'comision_mxn' => round($totalConIva, 2),
                'total_con_mp' => round($totalConMp, 2),
                'pago_mensual' => round($pagoMensual, 2),
            ];
        }
    }

    $showMercadoPago = $showMercadoPago ?? false;
@endphp

<div id="mp-section"
     style="{{ $isPreview ? '' : (!$showMercadoPago ? 'display:none;' : '') }}">

    @if($totalMxn)
        <div style="margin:0; text-align:center;">
            @if($isPreview)
                <img src="{{ asset('images/quoteLogos/Mercado_Pago.svg.png') }}"
                     style="height:80px; width:auto;">
            @else
                <img src="{{ public_path('images/quoteLogos/Mercado_Pago.svg.png') }}"
                     style="height:80px; width:auto;">
            @endif
        </div>

        <table style="width:100%; border-collapse:collapse; margin-bottom:12px; font-size:10px;">
            <thead>
            <tr style="background-color:#6b7280; color:#fff;">
                <th style="padding:6px 8px; text-align:center;">Plazo</th>
                <th style="padding:6px 8px; text-align:right;">Comisión MP (con IVA)</th>
                <th style="padding:6px 8px; text-align:right;">Total a pagar</th>
                <th style="padding:6px 8px; text-align:right;">Pago mensual aprox.</th>
            </tr>
            </thead>
            <tbody>
            @foreach($opcionesMp as $op)
                <tr style="{{ $loop->even ? 'background-color:#f3f4f6;' : 'background-color:#fff;' }}">
                    <td style="padding:5px 8px; text-align:center; font-weight:bold; color:#4b5563;">
                        {{ $op['meses'] }} meses
                    </td>
                    <td style="padding:5px 8px; text-align:right;">
                        {{ $op['comision_pct'] }}%
                        ($ {{ number_format($op['comision_mxn'], 2) }} MXN)
                    </td>
                    <td style="padding:5px 8px; text-align:right; font-weight:bold;">
                        $ {{ number_format($op['total_con_mp'], 2) }} MXN
                    </td>
                    <td style="padding:5px 8px; text-align:right; font-weight:bold; color:#4b5563;">
                        $ {{ number_format($op['pago_mensual'], 2) }} MXN
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <p style="font-size:8px; color:#6b7280; margin-bottom:12px;">
            * Las comisiones incluyen IVA (16%). Comisión fija adicional de $4.64 MXN por transacción.
            Sujeto a aprobación de Mercado Pago. Cálculo sobre el total en MXN de la cotización.
        </p>
    @else
        <p style="font-size:10px; color:#6b7280; margin-bottom:12px;">
            * Las opciones de Mercado Pago están disponibles únicamente para cotizaciones con precio en MXN.
        </p>
    @endif
</div>

<!-- ════════════════════════════════════════
     TÉRMINOS DE VENTA
════════════════════════════════════════ -->
<div class="terms-box">
    <div class="terms-title">TÉRMINOS DE VENTA</div>
    <p style="margin:0;">
        El precio de los productos está sujeto al tipo de cambio actualizado de acuerdo a lo publicado por el Banco de México
        en el Diario Oficial de la Federación, el tipo de cambio establecido permanecerá fijo hasta que la mercancía sea
        liquidada en su totalidad. Una vez confirmada la orden de compra, el Cliente tendrá que realizar un pago de contado
        ó un pago del 60% con respecto al precio total de la mercancía, el otro 40% tendrá que ser liquidado una vez recibida
        la mercancía.
    </p>
</div>

<!-- ════════════════════════════════════════
     NOTAS
════════════════════════════════════════ -->
{{-- Notas internas del banner (primera nota como "NOTA" del banner) --}}
@if($quoteVersion->notes->isNotEmpty() && ($isPreview || $showNotes))
    <div class="notes-section" id="notes-section" style="margin-top: 10px;">
        <div class="notes-title">NOTAS</div>
        <table class="notes-table">
            @foreach($quoteVersion->notes as $note)
                <tr>
                    <td class="note-num">{{ $loop->iteration }}</td>
                    <td class="banner-note-text">{{ $note->note }}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endif

<!-- ════════════════════════════════════════
     TOTALES + FIRMA + MÉTODOS DE PAGO
════════════════════════════════════════ -->
@php
    $fmt = fn(float $v, int $dec = 2) => number_format($v, $dec);
@endphp
<table class="totals-section-table" style="margin-top: 10px;">
    <tr>
        <!-- Precio USD -->
        @if($showUsdBox)
        <td class="totals-usd-cell" id="totals-usd-cell" style="width: {{ $boxWidth }}">
            <div class="totals-box">
                <div class="totals-box-title">
                    @if($isPreview)
                        <img src="{{ asset('images/quoteLogos/bandera_usa.png') }}" style="width:16px; height:12px; vertical-align:middle;">
                    @else
                        <img src="{{ public_path('images/quoteLogos/bandera_usa.png') }}" style="width:16px; height:12px; vertical-align:middle;">
                    @endif
                    PRECIO EN DÓLARES AMERICANOS
                </div>
                @if($summaryUsd)
                    <table class="totals-inner-table">
                        <tr>
                            <td>Total sin descuento</td>
                            <td class="amount">$ {{ $fmt($summaryUsd['total_no_discount']) }} USD</td>
                        </tr>
                        <tr>
                            <td>Descuento</td>
                            <td class="amount">- $ {{ $fmt($summaryUsd['discount']) }} USD</td>
                        </tr>
                        <tr>
                            <td>Envío</td>
                            <td class="amount">$ {{ $fmt($summaryUsd['shipping_price']) }} USD</td>
                        </tr>
                        <tr>
                            <td>Subtotal</td>
                            <td class="amount">$ {{ $fmt($summaryUsd['subtotal']) }} USD</td>
                        </tr>
                        <tr>
                            <td>(16%) I.V.A.</td>
                            <td class="amount">$ {{ $fmt($summaryUsd['tax']) }} USD</td>
                        </tr>
                        <tr class="totals-grand-row">
                            <td>Total a Pagar</td>
                            <td class="amount">$ {{ $fmt($summaryUsd['total']) }} USD</td>
                        </tr>
                    </table>
                @endif
            </div>
        </td>
        @endif

        <!-- Precio MXN (solo si existe en el snapshot) -->
        @if($showMxnBox)
            <td class="totals-mxn-cell" id="totals-mxn-cell" style="width: {{ $boxWidth }}">
                <div class="totals-box">
                    <div class="totals-box-title">
                        @if($isPreview)
                            <img src="{{ asset('images/quoteLogos/bandera_mx.png') }}" style="width:16px; height:12px; vertical-align:middle;">
                        @else
                            <img src="{{ public_path('images/quoteLogos/bandera_mx.png') }}" style="width:16px; height:12px; vertical-align:middle;">
                        @endif
                        PRECIO EN PESOS MEXICANOS
                    </div>
                    <table class="totals-inner-table">
                        <tr>
                            <td>Total sin descuento</td>
                            <td class="amount">$ {{ $fmt($summaryMxn['total_no_discount']) }} MXN</td>
                        </tr>
                        <tr>
                            <td>Descuento</td>
                            <td class="amount">- $ {{ $fmt($summaryMxn['discount']) }} MXN</td>
                        </tr>
                        <tr>
                            <td>Envío</td>
                            <td class="amount">$ {{ $fmt($summaryMxn['shipping_price']) }} MXN</td>
                        </tr>
                        <tr>
                            <td>Subtotal</td>
                            <td class="amount">$ {{ $fmt($summaryMxn['subtotal']) }} MXN</td>
                        </tr>
                        <tr>
                            <td>(16%) I.V.A.</td>
                            <td class="amount">$ {{ $fmt($summaryMxn['tax']) }} MXN</td>
                        </tr>
                        <tr class="totals-grand-row">
                            <td>Total a Pagar</td>
                            <td class="amount">$ {{ $fmt($summaryMxn['total']) }} MXN</td>
                        </tr>
                    </table>
                </div>
            </td>
        @endif

        <!-- Firma y métodos de pago -->
        <td class="totals-firma-cell">
            <div class="firma-name">ATTE. {{ strtoupper($quoteVersion->quote->user->name) }}</div>
            @if($isPreview)
                <img src="{{ asset('images/quoteLogos/metodos_pago.png') }}" alt="Métodos de pago">
            @else
                <img src="{{ public_path('images/quoteLogos/metodos_pago.png') }}" alt="Métodos de pago">
            @endif
        </td>
    </tr>
</table>

<!-- ════════════════════════════════════════
     ORDEN DE COMPRA
════════════════════════════════════════ -->
@if($quoteVersion->status === 'exitosa' && $quoteVersion->purchaseOrder)
    @php
        $purchaseOrder = $quoteVersion->purchaseOrder;
        $poRestan = $totalMxn !== null ? $totalMxn - (float) $purchaseOrder->advance_amount : null;
    @endphp
    <div class="terms-box" style="margin-top:10px;">
        <div class="terms-title">INFORMACIÓN DE ORDEN DE COMPRA</div>
        <table class="totals-inner-table" style="width:100%;">
            <tr>
                <td>Fecha de anticipo</td>
                <td class="amount">{{ $purchaseOrder->advance_paid_at?->format('d \d\e F \d\e Y') ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Monto</td>
                <td class="amount">{{ $purchaseOrder->advance_amount !== null ? number_format($purchaseOrder->advance_amount, 2) . ' MXN' : 'N/A' }}</td>
            </tr>
            <tr>
                <td>Medio de pago</td>
                <td class="amount">{{ $purchaseOrder->payment_method ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Referencia</td>
                <td class="amount">{{ $purchaseOrder->reference ?? 'N/A' }}</td>
            </tr>
            <tr class="totals-grand-row">
                <td>Restan</td>
                <td class="amount">{{ $poRestan !== null ? number_format($poRestan, 2) . ' MXN' : 'N/A' }}</td>
            </tr>
            <tr>
                <td>Fecha de liquidación</td>
                <td class="amount">{{ $purchaseOrder->settlement_date?->format('d \d\e F \d\e Y') ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>
@endif

{{-- ── FOOTER ───────────────────────────────────────────────── --}}
<div class="footer">
    Documento generado el {{ now()->format('d/m/Y H:i') }} &mdash; {{ $company->name }}
</div>

{{-- ── JS PARA PREVISUALIZACIÓN (solo en iframe) ───────────── --}}
@if($isPreview)
    <script>
        var _origClient = {
            name:    @json($resolvedName ?? '—'),
            company: @json($resolvedCompany ?? '—'),
            phone:   @json($resolvedPhone ?? '—'),
            email:   @json($resolvedEmail ?? '—'),
            address: @json($resolvedAddress ?? '—'),
        };

        window.addEventListener('message', function (event) {
            var data = event.data;

            if (typeof data.showNotes !== 'undefined') {
                var section = document.getElementById('notes-section');
                if (section) section.style.display = data.showNotes ? 'block' : 'none';
            }

            if (typeof data.showMercadoPago !== 'undefined') {
                var mpSection = document.getElementById('mp-section');
                if (mpSection) mpSection.style.display = data.showMercadoPago ? 'block' : 'none';
            }

            if (data.clientData) {
                var cd      = data.clientData;
                var name    = cd.editing ? cd.name    : _origClient.name;
                var company = cd.editing ? cd.company : _origClient.company;
                var phone   = cd.editing ? cd.phone   : _origClient.phone;
                var email   = cd.editing ? cd.email   : _origClient.email;
                var address = cd.editing ? cd.address : _origClient.address;

                // Visibilidad de filas
                var rowVisibility = {
                    'client-name-row':    cd.showName    !== false,
                    'client-company-row': cd.showCompany !== false,
                    'client-phone-row':   cd.showPhone   !== false,
                    'client-email-row':   cd.showEmail   !== false,
                    'client-address-row': cd.showAddress !== false,
                };
                Object.keys(rowVisibility).forEach(function(id) {
                    var el = document.getElementById(id);
                    if (el) el.style.display = rowVisibility[id] ? '' : 'none';
                });

                // Valores editados
                var nameEl = document.getElementById('client-name-value');
                if (nameEl) nameEl.textContent = name;

                var companyEl = document.getElementById('client-company-value');
                if (companyEl) companyEl.textContent = company;

                var phoneSpan = document.getElementById('client-phone-value');
                if (phoneSpan) phoneSpan.textContent = phone || '—';

                var emailSpan = document.getElementById('client-email-value');
                if (emailSpan) emailSpan.textContent = email || '—';

                var addressSpan = document.getElementById('client-address-value');
                if (addressSpan) addressSpan.textContent = address || '—';
            }
        });
    </script>
@endif

</body>
</html>