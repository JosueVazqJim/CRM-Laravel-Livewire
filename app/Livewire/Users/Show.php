<?php

namespace App\Livewire\Users;

use App\Models\Users\User;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Show User')]
class Show extends Component
{
    public User $user;

    public $stateName = null;

    public $municipalityName = null;

    public bool $active = false;

    public $roles = [];

    public function mount(User $user): void
    {
        // $user ya viene por route-model-binding (usa getRouteKeyName => 'slug')
        $this->user = $user->load(['phones', 'companies', 'address']);

        $this->active = (bool) $this->user->active;

        $this->roles = $user->roles->pluck('name')->toArray();

        // Cargar nombres de estado y municipio si existen
        if ($this->user->address) {
            $this->loadLocationNames();
        }
    }

    public function toggleActive(): void
    {
        $new = ! $this->active;

        try {
            // opcional: autorización aquí si corresponde
            $this->user->update(['active' => $new]);

            // refrescar el modelo para tener updated_at y el nuevo valor
            $this->user->refresh();

            // actualizar propiedad local
            $this->active = (bool) $this->user->active;
        } catch (\Throwable $e) {
            // log y revertir estado en UI en caso de error
            Log::error("Error toggling user active ({$this->user->id}): ".$e->getMessage());
            $this->active = ! $new; // revertir
        }
    }

    /**
     * Cargar los nombres de estado y municipio
     */
    protected function loadLocationNames()
    {
        $address = $this->user->address;

        if ($address->state) {
            $this->stateName = $address->stateRelation->name;
        }

        if ($address->municipality) {
            $this->municipalityName = $address->municipalityRelation->name;
        }
    }

    public function render()
    {
        return view('livewire.users.show');
    }
}
