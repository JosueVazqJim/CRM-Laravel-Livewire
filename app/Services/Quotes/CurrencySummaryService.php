<?php

namespace App\Services\Quotes;

use App\Models\Quotes\QuoteVersion;

class CurrencySummaryService
{
    /**
     * Calcula el resumen de totales para todas las monedas del snapshot.
     * USD siempre es la primera entrada.
     * Los valores en otras monedas se convierten con las tasas del snapshot.
     *
     * Estructura de cada entrada:
     *   currency          string  'USD' | 'MXN' | ...
     *   exchange_rate     float   tasa usada (1.0 para USD)
     *   total_no_discount float   suma de (qty × precio) sin aplicar descuentos
     *   discount          float   descuento total aplicado (positivo)
     *   subtotal_products float   total de productos con descuento (sin envío)
     *   shipping_price    float   costo de envío
     *   subtotal          float   subtotal_products + shipping_price
     *   tax               float   IVA (16%)
     *   total             float   subtotal + tax
     */
    public static function calculate(QuoteVersion $quoteVersion): array
    {
        $quoteVersion->loadMissing(['items', 'exchangeRates', 'concepts']);

        $subtotalUsd = (float) $quoteVersion->subtotal;
        $shippingUsd = (float) $quoteVersion->shipping_price;
        $taxUsd = (float) $quoteVersion->tax;
        $totalUsd = (float) $quoteVersion->total;
        $productsUsd = $subtotalUsd - $shippingUsd;

        // Total antes de descuentos: qty × precio_custom (igual que QuoteForm::itemsTotalNoDiscount),
        // incluyendo también los conceptos adicionales, que sí forman parte de $quoteVersion->subtotal.
        $noDiscountUsd = $quoteVersion->items->sum(
            fn ($item) => (float) $item->unit_price_converted_custom * (float) $item->quantity
        ) + $quoteVersion->concepts->sum(
            fn ($concept) => (float) $concept->unit_price * (float) $concept->quantity
        );
        $discountUsd = $noDiscountUsd - $productsUsd;

        $summary = [];

        // USD siempre primero
        $summary[] = [
            'currency' => 'USD',
            'exchange_rate' => 1.0,
            'total_no_discount' => $noDiscountUsd,
            'discount' => $discountUsd,
            'subtotal_products' => $productsUsd,
            'shipping_price' => $shippingUsd,
            'subtotal' => $subtotalUsd,
            'tax' => $taxUsd,
            'total' => $totalUsd,
        ];

        // Monedas del snapshot (pares desde USD)
        foreach ($quoteVersion->exchangeRates as $rate) {
            if ($rate->from_currency !== 'USD') {
                continue;
            }

            $r = (float) $rate->rate;

            $summary[] = [
                'currency' => $rate->to_currency,
                'exchange_rate' => $r,
                'total_no_discount' => $noDiscountUsd * $r,
                'discount' => $discountUsd * $r,
                'subtotal_products' => $productsUsd * $r,
                'shipping_price' => $shippingUsd * $r,
                'subtotal' => $subtotalUsd * $r,
                'tax' => $taxUsd * $r,
                'total' => $totalUsd * $r,
            ];
        }

        return $summary;
    }
}
