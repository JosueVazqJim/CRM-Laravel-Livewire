<?php

namespace App\Models\Products;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'product_serial_id',
        'user_id',
        'movement_type',
        'from_status',
        'to_status',
        'notes',
        'moved_at',
    ];

    protected $casts = [
        'moved_at' => 'datetime',
    ];

    public function serial(): BelongsTo
    {
        return $this->belongsTo(ProductSerial::class, 'product_serial_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
