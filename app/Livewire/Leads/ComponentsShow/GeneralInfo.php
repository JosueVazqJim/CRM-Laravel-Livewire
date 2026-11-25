<?php

namespace App\Livewire\Leads\ComponentsShow;

use App\Models\Companies\Company;
use App\Models\Leads\Lead;
use App\Models\States\State;
use Livewire\Component;
use Mary\Traits\Toast;

class GeneralInfo extends Component
{
    use Toast;

    public Lead $lead;

    public bool $editing = false;

    // Campos editables
    public string $name_company;

    public int $company_id;

    public int $state_id = 0;

    public string $last_contact;

    // Opciones para select
    public array $companyOptions = [];

    public array $states = [];

    public function mount(Lead $lead)
    {
        $this->lead = $lead;
        $this->fillFields();

        // Cargar empresas del usuario autenticado
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            // Admin ve todas las empresas activas
            $this->companyOptions = Company::where('active', true)
                ->get()
                ->map(fn ($company) => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'avatar' => $company->avatar_url,
                ])
                ->toArray();
        } else {
            // Vendedor solo ve sus empresas asignadas
            $this->companyOptions = $user->companies()
                ->where('active', true)
                ->get()
                ->map(fn ($company) => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'avatar' => $company->avatar_url,
                ])
                ->toArray();
        }

        $this->states = State::orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function fillFields()
    {
        $this->name_company = $this->lead->name_company ?? '';
        $this->company_id = $this->lead->company_id ?? 0;
        $this->state_id = $this->lead->state_id ?? 0;
        $this->last_contact = $this->lead->last_contact ?? '';
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
            'company_id' => ['required', 'exists:companies,id'],
            'name_company' => ['nullable', 'string', 'max:255'],
            'state_id' => ['nullable', 'exists:states,id'],
            'last_contact' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->lead->update([
                'company_id' => $this->company_id,
                'name_company' => $this->name_company,
                'state_id' => $this->state_id ?: null,
                'last_contact' => $this->last_contact,
            ]);

            $this->editing = false;

            // Éxito
            $this->success(
                title: '¡Campos de Información General actualizados!',
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
        return view('livewire.leads.components-show.general-info');
    }
}
