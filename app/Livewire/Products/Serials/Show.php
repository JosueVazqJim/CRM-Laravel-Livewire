<?php

namespace App\Livewire\Products\Serials;

use App\Models\Products\ProductSerial;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Detalles del Serial')]
class Show extends Component
{
    public ProductSerial $serial;

    public string $noteContent = '';

    public string $flagColor = '';

    public function mount(ProductSerial $serial): void
    {
        $this->serial = $serial->load([
            'product',
            'product.company',
            'product.category',
            'registeredBy',
            'soldBy',
            'quoteVersion',
            'movements',
        ]);

        $this->noteContent = $this->serial->notes ?? '';
        $this->flagColor = $this->serial->flag_color ?? '';
    }

    public function transitionTo(string $status, ?string $notes = null): void
    {
        try {
            $this->serial->transition($status, $notes);
            $this->serial->refresh();

            $this->dispatch('success', message: 'Serial actualizado correctamente.');
        } catch (\Throwable $e) {
            Log::error("Error transitioning serial ({$this->serial->id}): ".$e->getMessage());
            $this->dispatch('error', message: 'Error al actualizar el serial.');
        }
    }

    public function updateNotes(string $notes): void
    {
        try {
            $this->serial->update(['notes' => $notes ?: null]);
            $this->serial->refresh();

            $this->dispatch('success', message: 'Notas actualizadas correctamente.');
        } catch (\Throwable $e) {
            Log::error("Error updating notes ({$this->serial->id}): ".$e->getMessage());
            $this->dispatch('error', message: 'Error al actualizar las notas.');
        }
    }

    public function render()
    {
        return view('livewire.products.serials.show');
    }
}
