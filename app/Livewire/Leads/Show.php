<?php

namespace App\Livewire\Leads;

use App\Models\Leads\Lead;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Detalles del Lead')]
class Show extends Component
{
    public Lead $lead;

    public function mount(Lead $lead): void
    {
        $this->lead->load([
            'state',
            'company',
            'user',
            'billingInfo.address',
            'contactPeople.channels',
            'activities',
            'userAccess.user',
        ]);

        // CONTROL DE ACCESO
        $user = auth()->user();

        // Si no es admin
        if (! $user->hasRole('admin')) {
            // Verificar si es el creador del lead
            if ($this->lead->user_id !== $user->id) {
                // Verificar si tiene acceso compartido
                $hasAccess = $this->lead->userAccess()
                    ->where('user_id', $user->id)
                    ->exists();

                if (! $hasAccess) {
                    abort(403, 'No tienes permiso para acceder a este lead.');
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.leads.show');
    }
}
