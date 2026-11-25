<?php

namespace App\Livewire\Leads\ComponentsShow;

use App\Events\LeadAccessGranted;
use App\Models\Leads\Lead;
use App\Models\Leads\LeadUserAccess;
use App\Models\Users\User;
use Livewire\Component;
use Mary\Traits\Toast;

class UserAccess extends Component
{
    use Toast;

    public Lead $lead;

    public LeadUserAccess $leadUserAccess;

    public bool $editing = false;

    // Propiedades actualizables
    public array $selectedUsers = [];

    public array $userOptions = [];

    public function mount(Lead $lead)
    {
        $this->lead = $lead;

        $this->selectedUsers = $lead->userAccess->pluck('user_id')->toArray();

        $this->userOptions = \App\Models\Users\User::where('active', true)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['vendedor', 'admin']);
            })->get()->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->full_name,
            ])->toArray();
    }

    public function enableEditing()
    {
        $this->editing = true;
    }

    public function cancelEditing()
    {
        $this->editing = false;
    }

    public function update()
    {
        $this->validate([
            'selectedUsers' => ['nullable', 'array'],
            'selectedUsers.*' => ['exists:users,id'],
        ]);

        try {
            // IDs que YA tenían acceso antes de modificar
            $previousUserIds = $this->lead->userAccess()->pluck('user_id')->toArray();

            $this->lead->userAccess()->delete();

            foreach ($this->selectedUsers as $userId) {
                $this->lead->userAccess()->create([
                    'user_id' => $userId,
                ]);
            }

            // Solo notificar a usuarios que NO tenían acceso previamente
            $newUserIds = array_diff($this->selectedUsers, $previousUserIds);

            if (! empty($newUserIds)) {
                $users = User::whereIn('id', $newUserIds)->get();
                foreach ($users as $user) {
                    $user->notify(new \App\Notifications\LeadAccessGrantedNotification(
                        $this->lead,
                        auth()->user()
                    ));

                    LeadAccessGranted::dispatch($this->lead, auth()->user(), $user);
                }
            }

            $this->editing = false;

            $this->success(
                title: '¡Campos de Accesos de Usuario actualizados!',
                description: 'El lead ha sido guardado exitosamente.',
                timeout: 3000,
            );
        } catch (\Throwable $e) {
            $this->error(
                title: 'Error al actualizar el lead',
                description: $e->getMessage()
            );
        }
    }

    public function render()
    {
        return view('livewire.leads.components-show.user-access');
    }
}
