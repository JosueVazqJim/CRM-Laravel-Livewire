<?php

namespace App\Models\Products;

use App\Models\Companies\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Normalizer;

class Product extends Model
{
    protected $fillable = [
        'company_id',
        'product_type_id',
        'product_category_id',
        'name',
        'sku',
        'slug',
        'image_path',
        'currency',
        'price_regular',
        'price_min',
        'price_max',
        'installation_cost',
        'color',
        'short_description',
        'long_description',
        'active',
        // stock_count NO está en fillable: solo lo escribe el observer
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected $casts = [
        'price_regular' => 'decimal:2',
        'price_min' => 'decimal:2',
        'price_max' => 'decimal:2',
        'installation_cost' => 'decimal:2',
        'stock_count' => 'integer',
        'active' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relaciones
    // -------------------------------------------------------------------------

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ProductTag::class, 'product_tag');
    }

    public function specifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class)->orderBy('sort_order');
    }

    public function serials(): HasMany
    {
        return $this->hasMany(ProductSerial::class);
    }

    // Seriales disponibles en stock real y solo registros
    public function serialsInStock(): HasMany
    {
        return $this->hasMany(ProductSerial::class)->where('status', 'in_stock');
    }

    public function serialsTotal()
    {
        return $this->hasMany(ProductSerial::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeByCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('product_category_id', $categoryId);
    }

    public function scopeLowStock(Builder $query, int $threshold = 5): Builder
    {
        return $query->where('stock_count', '<=', $threshold)->where('stock_count', '>', 0);
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('stock_count', 0);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isLowStock(int $threshold = 5): bool
    {
        return $this->stock_count <= $threshold;
    }

    public function getImageUrlAttribute(): string
    {
        if (! $this->image_path) {
            return asset('images/no-image.jpg');
        }

        // Si la ruta ya empieza con 'http', es una URL completa
        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        // Si la ruta es de storage
        if (str_starts_with($this->image_path, 'products/')) {
            return asset('storage/'.$this->image_path);
        }

        // Si es una imagen en public
        return asset($this->image_path);
    }

    public function refreshStockCount(): void
    {
        $this->updateQuietly([
            'stock_count' => $this->serials()->where('status', 'in_stock')->count(),
        ]);
    }

    // En Product.php
    public function getFormattedDescriptionAttribute(): string
    {
        $text = $this->short_description ?: $this->long_description ?? '';

        // 1. Normalizar NFD → NFC (fix de acentos)
        $text = Normalizer::normalize($text, Normalizer::FORM_C);

        // 2. Quitar \n \r \t literales (como texto, no como saltos reales)
        $text = str_replace(['\\n', '\\r', '\\t'], ' ', $text);

        // 3. Colapsar múltiples espacios
        $text = preg_replace('/\s+/', ' ', $text);

        // 4. Decodificar entidades HTML
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Sin strip_tags — conserva <ul>, <li>, <b>, <p>, etc.
        return trim($text);
    }
}
