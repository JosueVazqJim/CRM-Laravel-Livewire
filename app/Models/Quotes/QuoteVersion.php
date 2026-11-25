<?php

namespace App\Models\Quotes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QuoteVersion extends Model
{
    protected $fillable = [
        'quote_id',
        'version_letter',
        'folio',
        'subtotal',
        'tax',
        'shipping_price',
        'total',
        'status',
        'closed_at',
        'source_version_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:4',
        'tax' => 'decimal:4',
        'shipping_price' => 'decimal:4',
        'total' => 'decimal:4',
        'closed_at' => 'datetime',
    ];

    const STATUS_EN_PROCESO = 'en_proceso';

    const STATUS_EN_VALIDACION = 'en_validacion';

    const STATUS_EXITOSA = 'exitosa';

    const STATUS_SIN_EXITO = 'sin_exito';

    const STATUS_CANCELADA = 'cancelada';

    const STATUS_PAPELERA = 'papelera';

    const TAX_RATE = 0.16; // IVA México

    protected static function booted(): void
    {
        static::saving(function (QuoteVersion $quoteVersion) {
            if (! $quoteVersion->isDirty('status')) {
                return;
            }

            $quoteVersion->closed_at = $quoteVersion->status === self::STATUS_EXITOSA
                ? ($quoteVersion->closed_at ?? now())
                : null;
        });
    }

    // -------------------------------------------------------------------------
    // Relaciones
    // -------------------------------------------------------------------------

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function exchangeRates(): HasMany
    {
        return $this->hasMany(QuoteExchangeRate::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(QuoteNote::class);
    }

    public function concepts(): HasMany
    {
        return $this->hasMany(QuoteConcept::class);
    }

    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class);
    }

    public function parentVersionQuote(): BelongsTo
    {
        return $this->belongsTo(QuoteVersion::class, 'source_version_id');
    }

    /** Recotizaciones generadas a partir de esta */
    public function childVersionQuotes(): HasMany
    {
        return $this->hasMany(QuoteVersion::class, 'source_version_id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Genera el folio según la convención:
     * slug de la emrpesa emisora + FECHA (YYYYMMDD) + LETRA_VERSION + ID_COTIZACION
     * Ejemplo: DD05052026B42
     */
    public static function generateFolio(Quote $quote, string $versionLetter): string
    {
        return $quote->company->slug.now()->format('dmY').$versionLetter.$quote->id;
    }

    // En QuoteVersion model

    const REVISION_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Genera la siguiente version_letter para una revisión dentro del árbol de revisiones.
     * Todo el árbol (sin importar de qué versión se derive) comparte una única
     * secuencia plana: A, B, C... cada versión guarda en source_version_id la
     * referencia a la versión de la que se originó. Límite: 26 revisiones por árbol.
     */
    public function nextChildVersionLetter(): string
    {
        $rootId = static::revisionTreeRootId($this->id);
        $usedLetters = static::revisionTreeLetters($rootId);

        $usedIndexes = array_map(
            fn ($letter) => strpos(static::REVISION_ALPHABET, $letter),
            $usedLetters
        );

        $nextIndex = max($usedIndexes) + 1;

        if ($nextIndex >= strlen(static::REVISION_ALPHABET)) {
            throw new \OverflowException('Se alcanzó el límite de 26 revisiones para esta cotización.');
        }

        return static::REVISION_ALPHABET[$nextIndex];
    }

    /**
     * Recorre source_version_id hacia arriba hasta encontrar la raíz del árbol de revisiones.
     */
    protected static function revisionTreeRootId(int $versionId): int
    {
        $current = QuoteVersion::findOrFail($versionId);

        while ($current->source_version_id) {
            $current = QuoteVersion::findOrFail($current->source_version_id);
        }

        return $current->id;
    }

    /**
     * Recolecta las version_letter de todas las versiones del árbol de revisiones
     * enraizado en $rootId (la raíz más todas sus revisiones, sin importar la rama).
     */
    protected static function revisionTreeLetters(int $rootId): array
    {
        $root = QuoteVersion::findOrFail($rootId);
        $letters = [$root->version_letter];
        $frontier = [$root->id];

        while (! empty($frontier)) {
            $children = QuoteVersion::whereIn('source_version_id', $frontier)
                ->get(['id', 'version_letter']);

            if ($children->isEmpty()) {
                break;
            }

            $letters = array_merge($letters, $children->pluck('version_letter')->all());
            $frontier = $children->pluck('id')->all();
        }

        return $letters;
    }

    /**
     * Para una Quote nueva desde 0 o recotización: letra raíz.
     * Busca entre QuoteVersions raíz (sin parent) de la misma quote.
     * A, B, C, D...
     */
    public static function nextRootVersionLetter(int $quoteId): string
    {
        $existing = QuoteVersion::where('quote_id', $quoteId)
            ->whereNull('source_version_id')
            ->pluck('version_letter')
            ->filter(fn ($l) => strlen($l) === 1)
            ->sort()
            ->values();

        if ($existing->isEmpty()) {
            return 'A';
        }

        $last = $existing->last();

        if ($last === 'Z') {
            throw new \OverflowException('Se alcanzó el límite de 26 versiones raíz.');
        }

        return chr(ord($last) + 1);
    }
}
