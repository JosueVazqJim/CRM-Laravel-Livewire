<?php

namespace App\Livewire\Products\Serials;

use App\Livewire\Forms\Products\ProductSerialForm;
use App\Models\Products\Product;
use App\Models\Quotes\QuoteVersion;
use App\Models\Users\User;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Crear Serial de Producto')]
class Create extends Component
{
    use Toast;

    public Product $product;

    public ProductSerialForm $serial;

    public array $sellerOptions = [];

    public array $quoteVersionOptions = [];

    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->serial = new ProductSerialForm($this, 'serial');
        $this->serial->product_id = $product->id;
        $this->serial->addBatchRow();

        $this->sellerOptions = User::where('active', true)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['vendedor', 'admin']);
            })->get()->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->full_name,
            ])->toArray();

        $this->quoteVersionOptions = QuoteVersion::query()
            ->orderByDesc('id')
            ->get(['id', 'folio'])
            ->map(fn ($qv) => ['id' => $qv->id, 'name' => $qv->folio])
            ->toArray();
    }

    public function addBatchRow(): void
    {
        $this->serial->addBatchRow();

        $this->dispatch('batch-row-added');
    }

    public function removeBatchRow(string $key): void
    {
        $this->serial->removeBatchRow($key);
    }

    public function save(): void
    {
        $this->validate();

        if ($this->serial->isBatchMode) {
            $created = $this->serial->storeBatch();

            $this->success(
                title: count($created).' seriales creados correctamente.',
                redirectTo: route('products.serials.index', $this->product->slug)
            );

            return;
        }

        $this->serial->store();

        $this->success(
            title: '¡Serial creado correctamente!',
            redirectTo: route('products.serials.index', $this->product->slug)
        );
    }

    public function render()
    {
        return view('livewire.products.serials.create');
    }
}
