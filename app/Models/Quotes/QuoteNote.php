<?php

namespace App\Models\Quotes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteNote extends Model
{
    protected $fillable = [
        'quote_version_id',
        'note',
    ];

    public function quoteVersion(): BelongsTo
    {
        return $this->belongsTo(QuoteVersion::class);
    }
}
