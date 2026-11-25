<?php

namespace App\Models\Quotes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteConcept extends Model
{
    protected $fillable = [
        'quote_version_id',
        'name',
        'description',
        'unit_price',
        'quantity',
        'discount',
    ];

    protected $casts = [
        'unit_price' => 'decimal:4',
        'quantity' => 'decimal:2',
        'discount' => 'decimal:2',
    ];

    public function quoteVersion(): BelongsTo
    {
        return $this->belongsTo(QuoteVersion::class);
    }

    public function lineTotal(): float
    {
        return (float) $this->unit_price * (float) $this->quantity;
    }

    public function lineTotalWithDiscount(): float
    {
        return $this->lineTotal() * (1 - (float) $this->discount / 100);
    }
}
