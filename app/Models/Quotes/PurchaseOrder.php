<?php

namespace App\Models\Quotes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrder extends Model
{
    protected $table = 'quote_purchase_orders';

    protected $fillable = [
        'quote_version_id',
        'advance_amount',
        'advance_paid_at',
        'payment_method',
        'reference',
        'settlement_date',
    ];

    protected $casts = [
        'advance_amount' => 'decimal:2',
        'advance_paid_at' => 'date',
        'settlement_date' => 'date',
    ];

    public function quoteVersion(): BelongsTo
    {
        return $this->belongsTo(QuoteVersion::class);
    }

    /** Saldo restante contra el total en MXN de la cotización */
    public function remaining(float $totalMxn): float
    {
        return $totalMxn - (float) $this->advance_amount;
    }
}
