<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización {{ $quoteVersion->folio }}</title>
    <style>
        @php
            $company   = $quoteVersion->quote->company;
            $themeRgb  = $company->theme_rgb ?? '74,163,97';
            // Si el theme viene como hex, convertirlo a RGB
            // Se asume que theme ya es "r,g,b" o "#rrggbb"
        @endphp

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --theme: {{ $themeRgb }};
            --theme-color: rgb({{ $themeRgb }});
            --theme-light: rgba({{ $themeRgb }}, 0.10);
            --theme-mid:   rgba({{ $themeRgb }}, 0.20);
            --dark: #1a1a1a;
            --gray: #555;
            --light-gray: #888;
            --border: #dde3ea;
            --row-alt: #f8f9fb;
        }

        body {
            font-family: 'Calibri', Calibri, sans-serif;
            font-size: 15px;
            color: var(--dark);
            background: #fff;
            padding: 28px 36px;
        }

        /* ─── HEADER ────────────────────────────────────────── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .header-left {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .header-left img {
            max-height: 48px;
            max-width: 150px;
            object-fit: contain;
        }

        .header-right {
            text-align: right;
            font-size: 10px;
            color: var(--gray);
            line-height: 1.7;
        }

        .header-right strong {
            color: var(--dark);
        }

        /* ─── BANNER DE COTIZACIÓN ──────────────────────────── */
        .quote-banner {
            background: var(--theme-light);
            border: 1px solid rgba({{ $themeRgb }}, 0.25);
            border-radius: 6px;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 22px;
        }

        .quote-banner-left .banner-title {
            font-size: 14px;
            font-weight: bold;
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
        }

        .quote-banner-left .folio {
            font-size: 11px;
            font-weight: bold;
            color: var(--dark);
            margin-bottom: 2px;
        }

        .quote-banner-left .date {
            font-size: 10px;
            color: var(--gray);
            margin-bottom: 8px;
        }

        .quote-banner-left .total-label {
            font-size: 10px;
            color: var(--gray);
        }

        .quote-banner-left .total-amount {
            font-size: 13px;
            font-weight: bold;
            color: var(--dark);
        }

        /* NOTA dentro del banner */
        .banner-note {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid rgba({{ $themeRgb }}, 0.2);
        }

        .banner-note-label {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: var(--theme-color);
            letter-spacing: 0.08em;
            margin-bottom: 3px;
        }

        .banner-note-text {
            font-size: 10px;
            color: var(--gray);
            line-height: 1.6;
        }

        /* DATOS DEL CLIENTE en el banner */
        .quote-banner-right {
            text-align: right;
        }

        .client-label {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: var(--theme-color);
            letter-spacing: 0.08em;
            margin-bottom: 6px;
        }

        .client-data {
            font-size: 10px;
            color: var(--dark);
            line-height: 1.8;
        }

        .client-data strong {
            color: var(--dark);
        }

        /* ─── TABLA DE PRODUCTOS ────────────────────────────── */
        .section-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: var(--light-gray);
            letter-spacing: 0.09em;
            margin-bottom: 6px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table thead tr {
            border-bottom: 2px solid var(--theme-color);
        }

        .items-table thead th {
            padding: 7px 10px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--gray);
        }

        .items-table thead th.right {
            text-align: right;
        }

        .items-table tbody tr {
            border-bottom: 1px solid var(--border);
        }

        .items-table tbody tr:nth-child(even) {
            background: var(--row-alt);
        }

        .items-table tbody td {
            padding: 10px 10px;
            vertical-align: top;
        }

        .items-table tbody td.right {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .product-name {
            font-weight: bold;
            color: var(--dark);
            margin-bottom: 2px;
        }

        .product-sku {
            font-size: 9px;
            color: var(--light-gray);
        }

        /* ─── TOTALES ───────────────────────────────────────── */
        .bottom-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 20px;
        }

        /* Totales en dos columnas al estilo del PDF */
        .totals-columns {
            display: flex;
            gap: 24px;
        }

        .totals-block {
            min-width: 210px;
        }

        .totals-block-header {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .flag-icon {
            width: 16px;
            height: 11px;
            display: inline-block;
            border-radius: 1px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 4px 0;
            font-size: 10px;
        }

        .totals-table td.label {
            color: var(--gray);
        }

        .totals-table td.amount {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .totals-table tr.total-row td {
            border-top: 1px solid var(--border);
            background: var(--theme-light);
            font-weight: bold;
            color: var(--dark);
            padding: 6px 4px;
        }

        /* Firma / métodos de pago */
        .payment-block {
            text-align: center;
            min-width: 140px;
        }

        .signature-name {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .payment-icons {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
        }

        .payment-pill {
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 9px;
            font-weight: bold;
            color: var(--gray);
            letter-spacing: 0.04em;
        }

        /* ─── TÉRMINOS DE VENTA ─────────────────────────────── */
        .terms-section {
            border-top: 1px solid var(--border);
            padding-top: 12px;
            margin-bottom: 16px;
        }

        .terms-section .section-title {
            margin-bottom: 5px;
        }

        .terms-text {
            font-size: 9.5px;
            color: var(--gray);
            line-height: 1.65;
            text-align: justify;
        }

        /* ─── NOTAS ─────────────────────────────────────────── */
        .notes-section {
            border-top: 1px solid var(--border);
            padding-top: 12px;
            margin-top: 4px;
        }

        .note-item {
            background: #fffbf0;
            border-left: 3px solid #d4a017;
            padding: 7px 11px;
            margin-bottom: 7px;
            border-radius: 2px;
            font-size: 10px;
            line-height: 1.6;
            color: #333;
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
    $company = $quoteVersion->quote->company;
    $lead    = $quoteVersion->quote->lead;
@endphp

{{-- ── HEADER ──────────────────────────────────────────────── --}}
<div class="header">
    <div class="header-left">
        <img src="{{ $company->avatar_url }}" alt="{{ $company->name }}">
    </div>
    <div class="header-right">
        {{ $company->companyAddress->colony}}, {{ $company->companyAddress->street}} {{ $company->companyAddress->exterior_number}} - {{ $company->companyAddress->interior_number}} <br>
        {{ $company->companyAddress->postal_code}} <br>
        {{ $company->companyAddress->stateRelation->name}}, {{ $company->companyAddress->municipalityRelation->name}}<br>
        <strong>Tel: </strong>
        @if($company->companyPhones)
            @foreach($company->companyPhones as $phone)
                {{ $phone->phone }},
            @endforeach
        @endif
    </div>

    <div class="header-right">
        <strong>Vendedor: </strong>{{$quoteVersion->quote->user->name}}<BR>
        <strong>Tel: </strong>
        @foreach($quoteVersion->quote->user->phones as $phone)
            {{ $phone->phone }},
        @endforeach

        <BR>
        <strong>Email: </strong>{{$quoteVersion->quote->user->email}}<BR>
    </div>
</div>

{{-- ── BANNER COTIZACIÓN + DATOS CLIENTE ──────────────────── --}}
<div class="quote-banner">

    {{-- Izquierda: datos de la cotización --}}
    <div class="quote-banner-left">
        <div class="banner-title">Cotización</div>
        <div class="folio">FOLIO: {{ $quoteVersion->folio }}</div>
        <div class="date">{{ \Carbon\Carbon::parse($quoteVersion->created_at)->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</div>
        <div class="total-label">Total:</div>
        <div class="total-amount">$ {{ number_format((float) $quoteVersion->total, 2) }} USD</div>

        {{-- Notas internas del banner (primera nota como "NOTA" del banner) --}}
        @if($quoteVersion->notes->isNotEmpty() && ($isPreview || $showNotes))
            <div class="banner-note" id="notes-section">
                <div class="banner-note-label">Nota</div>
                @foreach($quoteVersion->notes as $note)
                    <div class="banner-note-text">{{ $note->note }}</div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Derecha: datos del cliente --}}
    <div class="quote-banner-right">
        <div class="client-label">Datos del cliente</div>
        <div class="client-data">
            <strong>Nombre:</strong> {{ $lead->contact_name ?? $lead->name_company }}<br>
            <strong>Empresa:</strong> {{ $lead->name_company }}<br>
            @if($lead->phone ?? false)
                <strong>Teléfono:</strong> {{ $lead->phone }}<br>
            @endif
            @if($lead->email ?? false)
                <strong>Correo:</strong> {{ $lead->email }}<br>
            @endif
            @if($lead->address ?? false)
                {{ $lead->address }}
            @endif
        </div>
    </div>

</div>

{{-- ── TABLA DE PRODUCTOS ───────────────────────────────────── --}}
<table class="items-table">
    <thead>
    <tr>
        <th>Descripción</th>
        <th class="right">Cantidad</th>
        <th class="right">Precio USD</th>
        <th class="right">Total USD</th>
    </tr>
    </thead>
    <tbody>
    @foreach($quoteVersion->items as $item)
        <tr>
            <td>
                <div class="product-name">{{ $item->product->name }}</div>
                <div class="product-sku">SKU: {{ $item->product->sku }}</div>
            </td>
            <td class="right">{{ number_format((float) $item->quantity, 0) }}</td>
            <td class="right">$ {{ number_format((float) $item->unit_price_converted, 2) }}</td>
            <td class="right">$ {{ number_format($item->lineTotal(), 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

{{-- ── TOTALES + FIRMA ──────────────────────────────────────── --}}
<div class="bottom-section">

    <div class="totals-columns">
        {{-- Bloque de totales (se repite 2 veces como en el PDF original) --}}
        @for($col = 0; $col < 2; $col++)
            <div class="totals-block">
                <div class="totals-block-header">
                    🇺🇸 Precio en dólares americanos
                </div>
                <table class="totals-table">
                    <tr>
                        <td class="label">Subtotal:</td>
                        <td class="amount">$ {{ number_format((float) $quoteVersion->subtotal, 2) }}</td>
                    </tr>
                    @if((float) $quoteVersion->shipping_price > 0)
                        <tr>
                            <td class="label">Envío:</td>
                            <td class="amount">$ {{ number_format((float) $quoteVersion->shipping_price, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="label">IVA (16%):</td>
                        <td class="amount">$ {{ number_format((float) $quoteVersion->tax, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td class="label">Total a Pagar</td>
                        <td class="amount">$ {{ number_format((float) $quoteVersion->total, 2) }}</td>
                    </tr>
                </table>
            </div>
        @endfor
    </div>

    {{-- Firma y métodos de pago --}}
    <div class="payment-block">
        <div class="signature-name">Atte. {{ $company->seller_name ?? $company->name }}</div>
        <div class="payment-icons">
            <span class="payment-pill">VISA</span>
            <span class="payment-pill">Mastercard</span>
            <span class="payment-pill">PayPal</span>
            <span class="payment-pill">OXXO Pay</span>
        </div>
    </div>

</div>

{{-- ── TÉRMINOS DE VENTA ────────────────────────────────────── --}}
@if($quoteVersion->terms ?? false)
    <div class="terms-section">
        <div class="section-title">Términos de venta</div>
        <p class="terms-text">{{ $quoteVersion->terms }}</p>
    </div>
@endif

{{-- ── FOOTER ───────────────────────────────────────────────── --}}
<div class="footer">
    Documento generado el {{ now()->format('d/m/Y H:i') }} &mdash; {{ $company->name }}
</div>

{{-- ── JS PARA PREVISUALIZACIÓN (solo en iframe) ───────────── --}}
@if($isPreview)
    <script>
        window.addEventListener('message', function (event) {
            var section = document.getElementById('notes-section');
            if (!section || typeof event.data.showNotes === 'undefined') return;
            section.style.display = event.data.showNotes ? 'block' : 'none';
        });
    </script>
@endif

</body>
</html>