<?php

namespace App\Livewire\Products\Serials;

use App\Livewire\Forms\Products\ProductSerialForm;
use App\Models\Products\ProductSerial;
use App\Models\Quotes\QuoteVersion;
use App\Models\Users\User;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Editar Serial de Producto')]
class Update extends Component
{
    use Toast;

    public ProductSerialForm $serialForm;

    public ProductSerial $serial;

    public array $sellerOptions = [];

    public array $quoteVersionOptions = [];

    public function mount(ProductSerial $serial): void
    {
        $this->serial = $serial->load('product');
        $this->serialForm->setSerial($serial);

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

    public function save(): void
    {
        $this->validate();

        $this->serialForm->update();

        $this->success(
            title: '¡Serial actualizado correctamente!',
            redirectTo: route('products.serials.index', $this->serial->product->slug)
        );
    }

    public function render()
    {
        return view('livewire.products.serials.update');
    }
}
