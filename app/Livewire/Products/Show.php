<?php

namespace App\Livewire\Products;

use App\Models\Products\Product;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Detalles del Producto')]
class Show extends Component
{
    public Product $product;

    public bool $active = false;

    public function mount(Product $product): void
    {
        $this->product = $product->load([
            'company',
            'type',
            'category',
            'tags',
            'specifications',
            'serials',
        ]);

        $this->active = (bool) $this->product->active;
    }

    public function toggleActive(): void
    {
        $new = ! $this->active;

        try {
            $this->product->update(['active' => $new]);

            $this->product->refresh();

            $this->active = (bool) $this->product->active;
        } catch (\Throwable $e) {
            Log::error("Error toggling product active ({$this->product->id}): ".$e->getMessage());
            $this->active = ! $new;
        }
    }

    public function render()
    {
        return view('livewire.products.show');
    }
}
