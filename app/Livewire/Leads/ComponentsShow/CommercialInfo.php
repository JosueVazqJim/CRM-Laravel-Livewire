<?php

namespace App\Livewire\Leads\ComponentsShow;

use App\Models\Leads\Lead;
use Livewire\Component;
use Mary\Traits\Toast;

class CommercialInfo extends Component
{
    use Toast;

    public Lead $lead;

    public bool $editing = false;

    // Campos editables
    public string $urgency_level;

    public int $budget;

    public string $undermine_channel;

    public string $classification;

    public string $funnel_stage;

    public function mount(Lead $lead)
    {
        $this->lead = $lead;
        $this->fillFields();
    }

    public function fillFields()
    {
        $this->urgency_level = $this->lead->urgency_level ?? '';
        $this->budget = $this->lead->budget ?? 0;
        $this->undermine_channel = $this->lead->undermine_channel ?? '';
        $this->classification = $this->lead->classification ?? '';
        $this->funnel_stage = $this->lead->funnel_stage ?? '';
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
            'urgency_level' => ['nullable', 'string', 'max:100'],
            'budget' => ['nullable', 'integer', 'min:0'],
            'undermine_channel' => ['nullable', 'string', 'max:100'],
            'classification' => ['nullable', 'string', 'max:100'],
            'funnel_stage' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $this->lead->update([
                'urgency_level' => $this->urgency_level,
                'budget' => $this->budget,
                'undermine_channel' => $this->undermine_channel,
                'classification' => $this->classification,
                'funnel_stage' => $this->funnel_stage,
            ]);

            $this->editing = false;

            // Éxito
            $this->success(
                title: '¡Campos de Información Comercial actualizados!',
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
        return view('livewire.leads.components-show.commercial-info');
    }
}
