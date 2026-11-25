<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    protected $fillable = ['name', 'slug'];

    protected static function booted(): void
    {
        static::creating(fn ($m) => $m->slug ??= Str::slug($m->name));
        static::updating(fn ($m) => $m->slug = Str::slug($m->name));
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
