<?php

namespace App\Livewire\Products\Serials;

use App\Livewire\Traits\ClearsProperties;
use App\Models\Products\Product;
use App\Models\Products\ProductSerial;
use App\Models\Quotes\QuoteVersion;
use App\Models\Users\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Title('Gestión de Series de Productos')]
class Index extends Component
{
    use ClearsProperties, Toast, WithPagination;

    public ?Product $product = null;

    public string $user_id = '';

    public array $userOptions = [];

    public string $search = '';

    public string $status_filter = '';

    public string $flag_color_filter = '';

    public string $created_at = '';

    public string $updated_at = '';

    public string $purchased_at = '';

    public string $entered_at = '';

    public string $sold_at = '';

    public string $exited_at = '';

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public bool $showFilters = false;

    // Paginación
    public int $perPage = 15;

    public const PER_PAGE_OPTIONS = [15, 30, 50, 100];

    // Selección masiva y modal de "Cambiar estado"
    public const BULK_DELETE = 'delete';

    public array $selectedSerials = [];

    public bool $showBulkStatusModal = false;

    public string $bulk_status = '';

    public ?int $bulk_sold_by_user_id = null;

    public ?int $bulk_quote_version_id = null;

    public string $bulk_sold_at = '';

    public string $bulk_exited_at = '';

    public array $sellerOptions = [];

    public array $quoteVersionOptions = [];

    public function mount(?Product $product = null): void
    {
        $this->product = $product;

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

    public function openBulkStatusModal(): void
    {
        if (empty($this->selectedSerials)) {
            return;
        }

        $this->resetValidation();
        $this->bulk_status = '';
        $this->bulk_sold_by_user_id = null;
        $this->bulk_quote_version_id = null;
        $this->bulk_sold_at = now()->format('Y-m-d\TH:i');
        $this->bulk_exited_at = '';
        $this->showBulkStatusModal = true;
    }

    public function confirmBulkStatus(): void
    {
        $this->validate([
            'bulk_status' => ['required', 'in:'.implode(',', array_column($this->getBulkStatusOptions(), 'id'))],
        ], [], [
            'bulk_status' => 'nuevo estado',
        ]);

        match ($this->bulk_status) {
            self::BULK_DELETE => $this->bulkDelete(),
            ProductSerial::STATUS_SOLD => $this->bulkSold(),
            default => $this->bulkTransition($this->bulk_status),
        };
    }

    protected function bulkDelete(): void
    {
        if (! auth()->user()->can('seriales.eliminar')) {
            $this->error(title: 'No tienes permiso para eliminar seriales.');

            return;
        }

        try {
            $count = count($this->selectedSerials);

            DB::transaction(function () {
                // delete() por modelo para que el observer recalcule stock_count;
                // los stock_movements se eliminan en cascada por FK.
                ProductSerial::whereIn('id', $this->selectedSerials)
                    ->get()
                    ->each(fn (ProductSerial $serial) => $serial->delete());
            });

            $this->finishBulk("{$count} serial(es) eliminado(s) correctamente.", '¡Seriales eliminados!');
        } catch (\Throwable $e) {
            Log::error('Error en eliminación masiva de seriales: '.$e->getMessage());
            $this->error(title: 'Error al eliminar los seriales', description: $e->getMessage());
        }
    }

    protected function bulkTransition(string $status): void
    {
        try {
            $count = count($this->selectedSerials);

            DB::transaction(function () use ($status) {
                ProductSerial::whereIn('id', $this->selectedSerials)
                    ->where('status', '!=', $status)
                    ->get()
                    ->each(fn (ProductSerial $serial) => $serial->transition($status));
            });

            $this->finishBulk("{$count} serial(es) actualizado(s) correctamente.");
        } catch (\Throwable $e) {
            Log::error('Error en cambio masivo de estado de seriales: '.$e->getMessage());
            $this->error(title: 'Error al cambiar el estado', description: $e->getMessage());
        }
    }

    protected function finishBulk(string $description, string $title = '¡Estado actualizado!'): void
    {
        $this->selectedSerials = [];
        $this->showBulkStatusModal = false;

        $this->success(title: $title, description: $description, timeout: 3000);
    }

    protected function bulkSold(): void
    {
        $this->validate([
            'bulk_sold_by_user_id' => ['required', 'exists:users,id'],
            'bulk_quote_version_id' => ['nullable', 'exists:quote_versions,id'],
            'bulk_sold_at' => ['required', 'date'],
            'bulk_exited_at' => ['nullable', 'date'],
        ], [], [
            'bulk_sold_by_user_id' => 'usuario que vendió',
            'bulk_quote_version_id' => 'cotización',
            'bulk_sold_at' => 'fecha y hora de venta',
            'bulk_exited_at' => 'fecha de salida',
        ]);

        try {
            $count = count($this->selectedSerials);

            DB::transaction(function () {
                $serials = ProductSerial::whereIn('id', $this->selectedSerials)->get();

                foreach ($serials as $serial) {
                    if ($serial->status !== ProductSerial::STATUS_SOLD) {
                        $serial->transition(ProductSerial::STATUS_SOLD);
                    }

                    $serial->update([
                        'sold_by_user_id' => $this->bulk_sold_by_user_id,
                        'quote_version_id' => $this->bulk_quote_version_id,
                        'sold_at' => $this->bulk_sold_at,
                        'exited_at' => $this->bulk_exited_at ?: null,
                    ]);
                }
            });

            $this->finishBulk("{$count} serial(es) actualizado(s) correctamente.", '¡Seriales marcados como vendidos!');
        } catch (\Throwable $e) {
            Log::error('Error en marcado masivo de seriales: '.$e->getMessage());
            $this->error(
                title: 'Error al marcar como vendidos',
                description: $e->getMessage(),
            );
        }
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status_filter', 'flag_color_filter', 'created_at', 'updated_at',
            'purchased_at', 'entered_at', 'sold_at', 'exited_at', 'perPage',
        ])) {
            $this->resetPage();
        }
    }

    public function updatedPerPage(): void
    {
        if (! in_array($this->perPage, self::PER_PAGE_OPTIONS, true)) {
            $this->perPage = self::PER_PAGE_OPTIONS[0];
        }
    }

    public function getFilterCountProperty(): int
    {
        $count = 0;
        if (! empty($this->status_filter)) {
            $count++;
        }
        if (! empty($this->flag_color_filter)) {
            $count++;
        }
        if (! empty($this->created_at)) {
            $count++;
        }
        if (! empty($this->updated_at)) {
            $count++;
        }
        if (! empty($this->purchased_at)) {
            $count++;
        }
        if (! empty($this->entered_at)) {
            $count++;
        }
        if (! empty($this->sold_at)) {
            $count++;
        }
        if (! empty($this->exited_at)) {
            $count++;
        }

        return $count;
    }

    public function getSerialsProperty(): LengthAwarePaginator
    {
        $query = ProductSerial::query()
            ->with(['registeredBy', 'quoteVersion:id,folio']); // Eager load para evitar N+1

        if ($this->product) {
            $query->where('product_id', $this->product->id);
        }

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('serial_number', 'like', '%'.$this->search.'%')
                    ->orWhere('notes', 'like', '%'.$this->search.'%');
            });
        }

        if (! empty($this->status_filter)) {
            $query->where('status', $this->status_filter);
        }

        if (! empty($this->flag_color_filter)) {
            $query->where('flag_color', $this->flag_color_filter);
        }

        if (! empty($this->created_at)) {
            $query->whereDate('created_at', 'like', '%'.$this->created_at.'%');
        }

        if (! empty($this->updated_at)) {
            $query->whereDate('updated_at', 'like', '%'.$this->updated_at.'%');
        }

        if (! empty($this->purchased_at)) {
            $query->whereDate('purchased_at', 'like', '%'.$this->purchased_at.'%');
        }

        if (! empty($this->entered_at)) {
            $query->whereDate('entered_at', 'like', '%'.$this->entered_at.'%');
        }

        if (! empty($this->sold_at)) {
            $query->whereDate('sold_at', 'like', '%'.$this->sold_at.'%');
        }

        if (! empty($this->exited_at)) {
            $query->whereDate('exited_at', 'like', '%'.$this->exited_at.'%');
        }

        // Columnas permitidas para ordenar (solo columnas directas de la tabla)
        $sortableColumns = [
            'serial_number', 'status', 'purchase_price',
            'purchased_at', 'entered_stock_at', 'sold_at', 'created_at', 'updated_at',
        ];

        $sortColumn = in_array($this->sortBy['column'], $sortableColumns)
            ? $this->sortBy['column']
            : 'created_at'; // fallback seguro

        return $query
            ->orderBy($sortColumn, $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function headers(): array
    {
        return [
            ['key' => 'serial_number', 'label' => 'Serial', 'sortBy' => 'serial_number'],
            ['key' => 'status', 'label' => 'Estado', 'sortBy' => 'status'],
            //            ['key' => 'flag_color', 'label' => 'Bandera'],
            ['key' => 'purchase_price', 'label' => 'Precio Compra'],
            ['key' => 'purchased_at', 'label' => 'Fecha de compra', 'format' => ['date', 'd/m/Y']],
            ['key' => 'entered_stock_at', 'label' => 'Entrada al Stock', 'format' => ['date', 'd/m/Y']],
            ['key' => 'sold_at', 'label' => 'Fecha de Venta', 'sortBy' => 'sold_at', 'format' => ['date', 'd/m/Y H:i']],
            ['key' => 'quote_folio', 'label' => 'Folio Cotización'],
            ['key' => 'registeredBy.name', 'label' => 'Registrado por'],
        ];
    }

    public function getStatusOptions(): array
    {
        return [
            ['id' => 'in_stock', 'name' => 'En Stock'],
            ['id' => 'sold', 'name' => 'Vendido'],
            ['id' => 'damaged', 'name' => 'Dañado'],
            ['id' => 'defective', 'name' => 'Defectuoso'],
        ];
    }

    public function getBulkStatusOptions(): array
    {
        $options = [
            ['id' => ProductSerial::STATUS_IN_STOCK, 'name' => 'En Stock'],
            ['id' => ProductSerial::STATUS_SOLD, 'name' => 'Vendido'],
            ['id' => ProductSerial::STATUS_DAMAGED, 'name' => 'Dañado'],
            ['id' => ProductSerial::STATUS_DEFECTIVE, 'name' => 'Defectuoso'],
            ['id' => ProductSerial::STATUS_RETURN, 'name' => 'Retornado'],
        ];

        if (auth()->user()->can('seriales.eliminar')) {
            $options[] = ['id' => self::BULK_DELETE, 'name' => 'Eliminar'];
        }

        return $options;
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'serials' => $this->serials,
            'filterCount' => $this->filterCount,
            'statusOptions' => $this->getStatusOptions(),
            'bulkStatusOptions' => $this->getBulkStatusOptions(),
        ];
    }

    protected function filters(): array
    {
        return ['flag_color_filter', 'status_filter', 'created_at', 'updated_at', 'purchased_at', 'entered_at', 'sold_at', 'exited_at'];
    }

    public function render()
    {
        return view('livewire.products.serials.index');
    }
}
