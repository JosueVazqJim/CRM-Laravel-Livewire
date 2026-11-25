<?php

namespace App\Models\Quotes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteExchangeRate extends Model
{
    protected $fillable = [
        'quote_version_id',
        'from_currency',
        'to_currency',
        'rate',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
    ];

    public function quoteVersion(): BelongsTo
    {
        return $this->belongsTo(QuoteVersion::class);
    }

    /**
     * Puebla el snapshot de una versión tomando los tipos de cambio
     * más recientes de la tabla global `exchange_rates`.
     * Siempre incluye el par base→base con tasa 1.
     */
    public static function seedFromGlobalRates(QuoteVersion $version, string $baseCurrency): void
    {
        // Incluir la moneda base con tasa 1 (conversión identidad)
        $version->exchangeRates()->create([
            'from_currency' => $baseCurrency,
            'to_currency' => $baseCurrency,
            'rate' => 1.0,
        ]);

        // Obtener el tipo de cambio vigente más reciente para cada par
        $rates = ExchangeRate::where('base_currency', $baseCurrency)
            ->where('valid_at', '<=', now())
            ->orderBy('valid_at', 'desc')
            ->get()
            ->unique(fn ($r) => $r->target_currency);

        foreach ($rates as $rate) {
            $version->exchangeRates()->create([
                'from_currency' => $rate->target_currency,
                'to_currency' => $baseCurrency,
                'rate' => $rate->rate > 0 ? round(1 / $rate->rate, 6) : 0,
            ]);

            // También guardar el par directo (base → target) para referencia
            $version->exchangeRates()->create([
                'from_currency' => $baseCurrency,
                'to_currency' => $rate->target_currency,
                'rate' => $rate->rate,
            ]);
        }
    }
}
