<?php

namespace App\Livewire\Leads\ComponentsShow;

use App\Models\Leads\Lead;
use App\Models\Leads\LeadActivity;
use Livewire\Attributes\On;
use Livewire\Component;

class Activities extends Component
{
    public Lead $lead;

    public $activities;

    // Propiedades para formularios
    public bool $showModal = false;

    public bool $editing = false;

    public $activityId = null;

    // Campos del formulario
    public string $type_action = '';

    public $scheduled_at = null;

    // Tipos de actividades predefinidos
    public array $activityTypes = [
        'Llamada' => 'Llamada',
        'Reunión' => 'Reunión',
        'Correo' => 'Correo electrónico',
        'Propuesta' => 'Enviar propuesta',
        'Seguimiento' => 'Seguimiento',
        'Cotización' => 'Cotización',
        'Visita' => 'Visita comercial',
        'Demo' => 'Demostración',
        'Negociación' => 'Negociación',
        'Cierre' => 'Cierre',
        'Otro' => 'Otro',
    ];

    public function mount(Lead $lead)
    {
        $this->lead = $lead;
        $this->loadActivities();
    }

    public function loadActivities()
    {
        $this->activities = $this->lead->activities()
            ->where('user_id', auth()->id())
            ->orderBy('scheduled_at', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    #[On('actividad-actualizada')]
    public function refreshActivities()
    {
        $this->loadActivities();
    }

    // Abrir modal para nueva actividad
    public function createActivity()
    {
        $this->resetForm();
        $this->editing = false;
        $this->showModal = true;
    }

    // Abrir modal para editar
    public function editActivity($id)
    {
        $activity = LeadActivity::findOrFail($id);
        $this->activityId = $activity->id;
        $this->type_action = $activity->type_action;
        $this->scheduled_at = $activity->scheduled_at?->format('Y-m-d\TH:i');
        $this->editing = true;
        $this->showModal = true;
    }

    // Guardar nueva actividad
    public function saveActivity()
    {
        $this->validate([
            'type_action' => 'required|string|max:100',
            'scheduled_at' => 'nullable|date',
        ]);

        LeadActivity::create([
            'lead_id' => $this->lead->id,
            'user_id' => auth()->id(),
            'type_action' => $this->type_action,
            'scheduled_at' => $this->scheduled_at,
            'is_completed' => false,
        ]);

        $this->loadActivities();
        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('toast', message: 'Actividad creada correctamente', type: 'success');
    }

    // Actualizar actividad
    public function updateActivity()
    {
        $this->validate([
            'type_action' => 'required|string|max:100',
            'scheduled_at' => 'nullable|date',
        ]);

        $activity = LeadActivity::findOrFail($this->activityId);
        $activity->update([
            'type_action' => $this->type_action,
            'scheduled_at' => $this->scheduled_at,
        ]);

        $this->loadActivities();
        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('toast', message: 'Actividad actualizada', type: 'success');
    }

    // Cambiar estado de actividad (completar/reabrir)
    public function toggleComplete($id)
    {
        $activity = LeadActivity::findOrFail($id);

        if ($activity->is_completed) {
            $activity->update([
                'is_completed' => false,
                'completed_at' => null,
                'date_action' => null,
            ]);
            $message = 'Actividad reabierta';
        } else {
            $activity->update([
                'is_completed' => true,
                'completed_at' => now(),
                'date_action' => $activity->date_action ?? now(),
            ]);
            $message = 'Actividad completada';
        }

        $this->loadActivities();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    // Iniciar actividad (mover a "En Proceso")
    public function startActivity($id)
    {
        $activity = LeadActivity::findOrFail($id);
        $activity->update([
            'date_action' => now(),
        ]);

        $this->loadActivities();
        $this->dispatch('toast', message: 'Actividad iniciada', type: 'info');
    }

    // Reprogramar actividad rápidamente
    public function reschedule($id, $newDate)
    {
        $activity = LeadActivity::findOrFail($id);
        $activity->update([
            'scheduled_at' => $newDate,
            'date_action' => null, // Reiniciar si estaba en proceso
        ]);

        $this->loadActivities();
        $this->dispatch('toast', message: 'Actividad reprogramada', type: 'success');
    }

    // Eliminar actividad
    public function deleteActivity($id)
    {
        $activity = LeadActivity::findOrFail($id);
        $activity->delete();

        $this->loadActivities();
        $this->dispatch('toast', message: 'Actividad eliminada', type: 'warning');
    }

    private function resetForm()
    {
        $this->reset(['type_action', 'scheduled_at', 'activityId']);
    }

    // Método para clasificar actividades (similar a tu lógica original)
    public function getKanbanColumnsProperty()
    {
        $today = now();

        $activities = $this->activities->map(function ($activity) use ($today) {
            // Determinar estado
            if ($activity->is_completed) {
                $status = 'completed';
                $statusDate = $activity->completed_at;
                $statusIcon = 'o-check-circle';
                $statusColor = 'success';
                $statusBorder = 'border-success';
            } elseif ($activity->scheduled_at && $activity->scheduled_at->lt($today) && ! $activity->date_action) {
                $status = 'overdue';
                $statusDate = $activity->scheduled_at;
                $statusIcon = 'o-exclamation-triangle';
                $statusColor = 'error';
                $statusBorder = 'border-error';
            } elseif ($activity->scheduled_at && $activity->scheduled_at->gt($today)) {
                $status = 'scheduled';
                $statusDate = $activity->scheduled_at;
                $statusIcon = 'o-calendar';
                $statusColor = 'warning';
                $statusBorder = 'border-warning';
            } elseif ($activity->date_action && ! $activity->is_completed) {
                $status = 'in_progress';
                $statusDate = $activity->date_action;
                $statusIcon = 'o-play-circle';
                $statusColor = 'info';
                $statusBorder = 'border-info';
            } else {
                $status = 'pending';
                $statusDate = $activity->created_at;
                $statusIcon = 'o-clock';
                $statusColor = 'secondary';
                $statusBorder = 'border-secondary';
            }

            return (object) [
                'id' => $activity->id,
                'type_action' => $activity->type_action,
                'status' => $status,
                'statusDate' => $statusDate,
                'statusIcon' => $statusIcon,
                'statusColor' => $statusColor,
                'statusBorder' => $statusBorder,
                'scheduled_at' => $activity->scheduled_at,
                'date_action' => $activity->date_action,
                'completed_at' => $activity->completed_at,
                'created_at' => $activity->created_at,
            ];
        });

        return [
            'pending_overdue' => [
                'title' => 'Pendientes y Vencidas',
                'icon' => 'o-exclamation-circle',
                'color' => 'error',
                'activities' => $activities->filter(fn ($a) => in_array($a->status, ['pending', 'overdue'])),
            ],
            //            'in_progress' => [
            //                'title' => 'En Proceso',
            //                'icon' => 'o-play-circle',
            //                'color' => 'info',
            //                'activities' => $activities->filter(fn ($a) => $a->status === 'in_progress'),
            //            ],
            'scheduled' => [
                'title' => 'Agendadas',
                'icon' => 'o-calendar',
                'color' => 'warning',
                'activities' => $activities->filter(fn ($a) => $a->status === 'scheduled'),
            ],
            'completed' => [
                'title' => 'Completadas',
                'icon' => 'o-check-circle',
                'color' => 'success',
                'activities' => $activities->filter(fn ($a) => $a->status === 'completed'),
            ],
        ];
    }

    public function render()
    {
        return view('livewire.leads.components-show.activities', [
            'kanbanColumns' => $this->getKanbanColumnsProperty(),
        ]);
    }
}
