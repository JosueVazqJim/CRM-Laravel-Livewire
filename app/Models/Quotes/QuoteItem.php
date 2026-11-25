<?php

namespace App\Models\Quotes;

use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends Model
{
    protected $fillable = [
        'quote_version_id',
        'product_id',
        'quantity',
        'unit_price_original',
        'currency_original',
        'unit_price_converted',
        'exchange_rate_used',
        'custom_price',
        'unit_price_converted_custom',
        'discount',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price_original' => 'decimal:4',
        'unit_price_converted' => 'decimal:4',
        'exchange_rate_used' => 'decimal:6',
        'custom_price' => 'boolean',
        'unit_price_converted_custom' => 'decimal:4',
        'discount' => 'decimal:4',
    ];

    // -------------------------------------------------------------------------
    // Relaciones
    // -------------------------------------------------------------------------

    public function quoteVersion(): BelongsTo
    {
        return $this->belongsTo(QuoteVersion::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Importe total del renglón en moneda base */
    public function lineTotal(): float
    {
        return round((float) $this->unit_price_converted * (float) $this->quantity, 4);
    }

    public function unitPriceConvertedDiscount(): float
    {
        return round(
            (float) $this->unit_price_converted - (((float) $this->unit_price_converted / 100) * (float) $this->discount), 4);
    }

    /** Importe total del renglón aplicando el descuento por unidad */
    public function lineTotalWithDiscount(): float
    {
        return round($this->unitPriceConvertedDiscount() * (float) $this->quantity, 4);
    }
}
