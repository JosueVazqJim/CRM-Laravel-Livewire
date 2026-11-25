<?php

namespace App\Models\Products;

use App\Models\Quotes\QuoteVersion;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductSerial extends Model
{
    // Posibles estados del ciclo de vida
    const STATUS_IN_STOCK = 'in_stock';

    const STATUS_SOLD = 'sold';

    const STATUS_DAMAGED = 'damaged';

    const STATUS_DEFECTIVE = 'defective';

    const STATUS_RETURN = 'return';

    protected $fillable = [
        'product_id',
        'user_id',
        'sold_by_user_id',
        'quote_version_id',
        'serial_number',
        'status',
        'flag_color',
        'purchase_price',
        'purchased_at',
        'entered_stock_at',
        'sold_at',
        'exited_at',
        'notes',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'purchased_at' => 'date',
        'entered_stock_at' => 'date',
        'sold_at' => 'datetime',
        'exited_at' => 'date',
    ];

    // -------------------------------------------------------------------------
    // Relaciones
    // -------------------------------------------------------------------------

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function soldBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by_user_id');
    }

    public function quoteVersion(): BelongsTo
    {
        return $this->belongsTo(QuoteVersion::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest('moved_at');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_STOCK);
    }

    public function scopeSold(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SOLD);
    }

    public function scopeDefective(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_DAMAGED, self::STATUS_DEFECTIVE]);
    }

    public function scopeReturn(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_RETURN]);
    }

    public function scopeFlagged(Builder $query): Builder
    {
        return $query->whereNotNull('flag_color');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_IN_STOCK;
    }

    public function isDefective(): bool
    {
        return in_array($this->status, [self::STATUS_DAMAGED, self::STATUS_DEFECTIVE]);
    }

    /**
     * Cambia el estado y registra el movimiento en stock_movements.
     * Usar este método en lugar de actualizar status directamente.
     */
    public function transition(string $toStatus, ?string $notes = null): void
    {
        $fromStatus = $this->status;

        $this->update([
            'status' => $toStatus,
            'sold_at' => $toStatus === self::STATUS_SOLD ? now() : $this->sold_at,
            'exited_at' => in_array($toStatus, [self::STATUS_DAMAGED, self::STATUS_DEFECTIVE])
                ? now()
                : $this->exited_at,
        ]);

        $this->movements()->create([
            'user_id' => auth()->id(),
            'movement_type' => $toStatus,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'notes' => $notes,
            'moved_at' => now(),
        ]);
    }
}
