<?php

namespace App\Observers;

use App\Models\Products\Product;
use Illuminate\Support\Str;

class ProductObserver
{
    public function creating(Product $product): void
    {
        if (empty($product->slug)) {
            $product->slug = Str::slug($product->name.'-'.$product->sku);
        }
    }

    public function updating(Product $product): void
    {
        if ($product->isDirty('name') || $product->isDirty('sku')) {
            $product->slug = Str::slug($product->name.'-'.$product->sku);
        }
    }
}
