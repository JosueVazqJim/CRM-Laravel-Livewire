<?php

namespace App\Livewire\Serials;

use App\Models\Products\ProductSerial;
use App\Models\Quotes\QuoteVersion;
use App\Models\Users\User;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Mary\Traits\Toast;

class SaleInfo extends Component
{
    use Toast;

    public ProductSerial $serial;

    public bool $editing = false;

    // Campos editables
    public ?int $sold_by_user_id = null;

    public ?int $quote_version_id = null;

    public string $sold_at = '';

    public string $exited_at = '';

    // Opciones para select
    public array $sellerOptions = [];

    public array $quoteVersionOptions = [];

    public function mount(ProductSerial $serial): void
    {
        $this->serial = $serial;

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

        $this->fillFields();
    }

    public function fillFields(): void
    {
        $this->sold_by_user_id = $this->serial->sold_by_user_id;
        $this->quote_version_id = $this->serial->quote_version_id;
        $this->sold_at = $this->serial->sold_at?->format('Y-m-d\TH:i') ?? '';
        $this->exited_at = $this->serial->exited_at?->format('Y-m-d') ?? '';
    }

    public function enableEditing(): void
    {
        $this->fillFields();
        $this->editing = true;
    }

    public function cancelEditing(): void
    {
        $this->editing = false;
        $this->fillFields();
    }

    public function save(): void
    {
        $this->validate([
            'sold_by_user_id' => ['required', 'exists:users,id'],
            'quote_version_id' => ['nullable', 'exists:quote_versions,id'],
            'sold_at' => ['required', 'date'],
            'exited_at' => ['nullable', 'date'],
        ], [], [
            'sold_by_user_id' => 'usuario que vendió',
            'quote_version_id' => 'cotización',
            'sold_at' => 'fecha y hora de venta',
            'exited_at' => 'fecha de salida',
        ]);

        try {
            $this->serial->update([
                'sold_by_user_id' => $this->sold_by_user_id,
                'quote_version_id' => $this->quote_version_id,
                'sold_at' => $this->sold_at,
                'exited_at' => $this->exited_at ?: null,
            ]);

            $this->success(
                title: '¡Información de venta actualizada!',
                description: 'Los datos de la venta se guardaron correctamente.',
                redirectTo: route('products.serials.show', $this->serial),
            );
        } catch (\Throwable $e) {
            Log::error("Error registrando venta del serial ({$this->serial->id}): ".$e->getMessage());
            $this->error(
                title: 'Error al registrar la venta',
                description: $e->getMessage(),
            );
        }
    }

    public function render()
    {
        return view('livewire.serials.sale-info');
    }
}
