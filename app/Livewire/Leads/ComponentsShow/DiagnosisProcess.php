<?php

namespace App\Livewire\Leads\ComponentsShow;

use App\Models\Leads\Lead;
use Livewire\Component;
use Mary\Traits\Toast;

class DiagnosisProcess extends Component
{
    use Toast;

    public Lead $lead;

    public bool $editing = false;

    // Campos editables
    public string $problem_detected;

    public bool $demo_offered;

    public bool $first_contact;

    public bool $differentiator_explained;

    public bool $diagnosis_made;

    public function mount(Lead $lead)
    {
        $this->lead = $lead;
        $this->fillFields();
    }

    public function fillFields()
    {
        $this->problem_detected = $this->lead->problem_detected ?? '';
        $this->demo_offered = $this->lead->demo_offered ?? false;
        $this->first_contact = $this->lead->first_contact ?? false;
        $this->differentiator_explained = $this->lead->differentiator_explained ?? false;
        $this->diagnosis_made = $this->lead->diagnosis_made ?? false;
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
            'problem_detected' => ['nullable', 'string', 'max:5000'],
            'demo_offered' => ['boolean'],
            'first_contact' => ['boolean'],
            'differentiator_explained' => ['boolean'],
            'diagnosis_made' => ['boolean'],
        ]);

        try {
            $this->lead->update([
                'problem_detected' => $this->problem_detected,
                'demo_offered' => $this->demo_offered,
                'first_contact' => $this->first_contact,
                'differentiator_explained' => $this->differentiator_explained,
                'diagnosis_made' => $this->diagnosis_made,
                'probability' => $this->lead->getClosingProbabilityAttribute(),
            ]);

            $this->lead->update([
                'probability' => $this->lead->getClosingProbabilityAttribute(),
            ]);

            $this->editing = false;

            // Éxito
            $this->success(
                title: '¡Campos de Diagnóstico y Proceso actualizados!',
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
        return view('livewire.leads.components-show.diagnosis-process');
    }
}
