<?php

namespace App\Models\Quotes;

use App\Models\Companies\Company;
use App\Models\Leads\Lead;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    /** @use HasFactory<\Database\Factories\Quotes\QuoteFactory> */
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'user_id',
        'company_id',
        'base_currency',
        'parent_quote_id',
    ];

    // -------------------------------------------------------------------------
    // Relaciones
    // -------------------------------------------------------------------------

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** Cotización padre en el flujo de recotización */
    public function parentQuote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'parent_quote_id');
    }

    /** Recotizaciones generadas a partir de esta */
    public function childQuotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'parent_quote_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(QuoteVersion::class)->orderBy('version_letter');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Devuelve la versión más reciente (última letra) */
    public function latestVersion(): ?QuoteVersion
    {
        return $this->versions()->latest('id')->first();
    }
}
