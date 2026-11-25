<?php

namespace App\Livewire\Leads\ComponentsShow;

use App\Models\Leads\Lead;
use Livewire\Component;
use Mary\Traits\Toast;

class ClientStatus extends Component
{
    use Toast;

    public Lead $lead;

    public bool $editing = false;

    // Campos editables
    public string $client_progress;

    public string $client_status;

    public string $pay_status;

    public string $after_sales;

    public function mount(Lead $lead)
    {
        $this->lead = $lead;
        $this->fillFields();
    }

    public function fillFields()
    {
        $this->client_progress = $this->lead->client_progress ?? '';
        $this->client_status = $this->lead->client_status ?? '';
        $this->pay_status = $this->lead->pay_status ?? '';
        $this->after_sales = $this->lead->after_sales ?? '';
    }

    public function enableEditing()
    {
        $this->editing = true;
    }

    public function cancelEditing()
    {
        $this->editing = false;
        $this->fillFields(); // Restaurar valores originales
    }

    public function update()
    {
        $this->validate([
            'client_progress' => ['nullable', 'string', 'max:100'],
            'client_status' => ['nullable', 'string', 'max:100'],
            'pay_status' => ['nullable', 'string', 'max:100'],
            'after_sales' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $this->lead->update([
                'client_progress' => $this->client_progress,
                'client_status' => $this->client_status,
                'pay_status' => $this->pay_status,
                'after_sales' => $this->after_sales,
            ]);

            $this->editing = false;

            // Éxito
            $this->success(
                title: '¡Campos de Estado del Cliente actualizados!',
                description: 'El lead ha sido guardado exitosamente.',
                timeout: 3000,
            );
        } catch (\Throwable $e) {
            $this->error(
                title: 'Error al actualizar el lead',
                // description: 'Ocurrió un error durante el intento de creación del lead. Contacte con el administrador.',
                description: $e->getMessage()
            );
        }
    }

    public function render()
    {
        return view('livewire.leads.components-show.client-status');
    }
}
