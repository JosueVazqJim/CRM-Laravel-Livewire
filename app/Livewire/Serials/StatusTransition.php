<?php

namespace App\Livewire\Serials;

use App\Models\Products\ProductSerial;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Mary\Traits\Toast;

class StatusTransition extends Component
{
    use Toast;

    public ProductSerial $serial;

    #[\Livewire\Attributes\Computed]
    public function statusColors()
    {
        return [
            'in_stock' => 'badge-success',
            'sold' => 'badge-info',
            'damaged' => 'badge-error',
            'defective' => 'badge-error',
            'return' => 'badge-warning',
        ];
    }

    #[\Livewire\Attributes\Computed]
    public function statusLabels()
    {
        return [
            'in_stock' => 'En Stock',
            'sold' => 'Vendido',
            'damaged' => 'Dañado',
            'defective' => 'Defectuoso',
            'return' => 'Retornado',
        ];
    }

    #[\Livewire\Attributes\Computed]
    public function statusActions()
    {
        return [
            'in_stock' => [
                ['status' => 'sold', 'label' => 'Marcar como Vendido', 'icon' => 'o-shopping-cart'],
                ['status' => 'damaged', 'label' => 'Marcar como Dañado', 'icon' => 'o-exclamation-triangle'],
                ['status' => 'defective', 'label' => 'Marcar como Defectuoso', 'icon' => 'o-exclamation-circle'],
            ],
            'sold' => [
                ['status' => 'in_stock', 'label' => 'Retornar al Stock', 'icon' => 'o-exclamation-triangle'],
                ['status' => 'return', 'label' => 'Marcar como Retorno', 'icon' => 'o-arrow-uturn-left'],
            ],
            'damaged' => [
                ['status' => 'in_stock', 'label' => 'Retornar al Stock', 'icon' => 'o-exclamation-triangle'],
            ],
            'defective' => [
                ['status' => 'in_stock', 'label' => 'Retornar al Stock', 'icon' => 'o-exclamation-triangle'],
            ],
            'return' => [
                ['status' => 'in_stock', 'label' => 'Retornar al Stock', 'icon' => 'o-exclamation-triangle'],
            ],
        ];
    }

    public function transitionTo(string $status, ?string $notes = null): void
    {
        try {
            $this->serial->transition($status, $notes);

            $this->success(
                title: 'Serial actualizado correctamente.',
                redirectTo: route('products.serials.show', $this->serial),
            );
        } catch (\Throwable $e) {
            Log::error("Error transitioning serial ({$this->serial->id}): ".$e->getMessage());
            $this->error(title: 'Error al actualizar el serial.', description: $e->getMessage());
        }
    }

    public function deleteSerial(): void
    {
        // El modal nativo vive en el top layer del navegador y taparía el toast: se cierra antes de mostrarlo.
        $this->js("document.getElementById('delete-serial-modal-{$this->serial->id}')?.close()");

        if (! auth()->user()->can('seriales.eliminar')) {
            $this->error(title: 'No tienes permiso para eliminar seriales.');

            return;
        }

        try {
            $product = $this->serial->product;
            $serialNumber = $this->serial->serial_number;

            // Los stock_movements se eliminan en cascada por FK y el observer recalcula stock_count.
            $this->serial->delete();

            $this->success(
                title: 'Serial eliminado',
                description: "El serial {$serialNumber} fue eliminado correctamente.",
                redirectTo: route('products.serials.index', $product),
            );
        } catch (\Throwable $e) {
            Log::error("Error deleting serial ({$this->serial->id}): ".$e->getMessage());
            $this->error(title: 'Error al eliminar el serial.', description: $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.serials.status-transition');
    }
}
