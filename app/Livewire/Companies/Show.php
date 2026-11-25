<?php

namespace App\Livewire\Companies;

use App\Models\Companies\Company;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Show Company')]
class Show extends Component
{
    public Company $company;

    public $stateName = null;

    public $municipalityName = null;

    public bool $active = false;

    public function mount(Company $company): void
    {
        // $user ya viene por route-model-binding (usa getRouteKeyName => 'slug')
        $this->company = $company->load(['companyPhones', 'companyAddress', 'companyEmails', 'modules', 'users', 'defaultNotes']);

        $this->active = (bool) $this->company->active;

        // Cargar nombres de estado y municipio si existen
        if ($this->company->companyAddress) {
            $this->loadLocationNames();
        }
    }

    public function toggleActive(): void
    {
        $new = ! $this->active;

        try {
            // opcional: autorización aquí si corresponde
            $this->company->update(['active' => $new]);

            // refrescar el modelo para tener updated_at y el nuevo valor
            $this->company->refresh();

            // actualizar propiedad local
            $this->active = (bool) $this->company->active;
        } catch (\Throwable $e) {
            // log y revertir estado en UI en caso de error
            Log::error("Error toggling company active ({$this->company->id}): ".$e->getMessage());
            $this->active = ! $new; // revertir
        }
    }

    /**
     * Cargar los nombres de estado y municipio
     */
    protected function loadLocationNames()
    {
        $address = $this->company->companyAddress;

        if ($address->state) {
            $this->stateName = $address->stateRelation->name;
        }

        if ($address->municipality) {
            $this->municipalityName = $address->municipalityRelation->name;
        }
    }

    public function render()
    {
        return view('livewire.companies.show');
    }
}
