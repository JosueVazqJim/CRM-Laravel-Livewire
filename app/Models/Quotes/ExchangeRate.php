<?php

namespace App\Models\Quotes;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'base_currency',
        'target_currency',
        'rate',
        'valid_at',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'valid_at' => 'datetime',
    ];

    /**
     * Devuelve el tipo de cambio vigente más reciente para un par de monedas.
     * Retorna null si no existe registro.
     */
    public static function current(string $base, string $target): ?self
    {
        if ($base === $target) {
            // Devuelve un objeto sintético con tasa 1 para simplificar el consumo
            $synthetic = new self([
                'base_currency' => $base,
                'target_currency' => $target,
                'rate' => 1.0,
                'valid_at' => now(),
            ]);

            return $synthetic;
        }

        return static::where('base_currency', $base)
            ->where('target_currency', $target)
            ->where('valid_at', '<=', now())
            ->orderBy('valid_at', 'desc')
            ->first();
    }

    // devuelve todas las mendas vigentes
    public static function allCurrent()
    {
        return static::where('valid_at', '<=', now())
            ->orderBy('valid_at', 'desc')
            ->get()
            ->unique()
            ->unique(fn ($r) => $r->target_currency);
    }
}
