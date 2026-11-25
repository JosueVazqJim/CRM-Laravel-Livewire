<?php

namespace App\Livewire\Dashboard;

use App\Models\Leads\LeadActivity;
use Illuminate\Support\Collection;
use Livewire\Component;

class PendingActivitiesKanban extends Component
{
    private function loadActivities(): Collection
    {
        $user = auth()->user();

        return LeadActivity::with(['lead:id,name_company'])
            ->where('is_completed', false)
            ->where('user_id', $user->id)
            ->whereNotNull('scheduled_at')
            // El admin puede agendar en cualquier lead; el vendedor solo ve las de leads propios o compartidos
            ->when(! $user->hasRole('admin'), fn ($query) => $query->whereHas('lead', function ($q) use ($user) {
                $q->where(function ($sq) use ($user) {
                    $sq->where('user_id', $user->id)
                        ->orWhereHas('userAccess', fn ($qa) => $qa->where('user_id', $user->id)
                        );
                });
            }))
            ->get();
    }

    private function classifyActivity(LeadActivity $activity): array
    {
        $base = [
            'id' => $activity->id,
            'lead_id' => $activity->lead_id,
            'lead_name' => $activity->lead?->name_company ?? '—',
            'type_action' => $activity->type_action,
            'description' => $activity->description ?? null,
            'scheduled_at' => $activity->scheduled_at,
            'date_action' => $activity->date_action,
            'created_at' => $activity->created_at,
        ];

        if ($activity->isOverdue()) {
            $diff = $activity->scheduled_at->diff(now());

            if ($diff->days > 0) {
                $overdueText = "Vencida hace {$diff->days} día".($diff->days > 1 ? 's' : '');
            } elseif ($diff->h > 0) {
                $overdueText = "Vencida hace {$diff->h} hora".($diff->h > 1 ? 's' : '');
            } elseif ($diff->i > 0) {
                $overdueText = "Vencida hace {$diff->i} minuto".($diff->i > 1 ? 's' : '');
            } else {
                $overdueText = 'Vencida recientemente';
            }

            return array_merge($base, [
                'status' => 'overdue',
                'statusDate' => $activity->scheduled_at,
                'statusIcon' => 'o-exclamation-triangle',
                'statusColor' => 'error',
                'statusBorder' => 'border-error',
                'overdueText' => $overdueText,
            ]);
        }

        return array_merge($base, [
            'status' => 'scheduled',
            'statusDate' => $activity->scheduled_at,
            'statusIcon' => 'o-calendar',
            'statusColor' => 'warning',
            'statusBorder' => 'border-warning',
        ]);
    }

    private function buildKanbanColumns(Collection $activities): array
    {
        $classified = $activities->map(fn ($a) => $this->classifyActivity($a));

        $overdue = $classified->where('status', 'overdue');
        $scheduled = $classified->where('status', 'scheduled');

        return [
            'overdue' => [
                'title' => 'Vencidas',
                'icon' => 'o-exclamation-circle',
                'color' => 'error',
                'activities' => $overdue->sortByDesc('statusDate')->values(),
            ],
            'scheduled' => [
                'title' => 'Agendadas',
                'icon' => 'o-calendar',
                'color' => 'warning',
                'activities' => $scheduled->sortBy('statusDate')->values(),
            ],
        ];
    }

    public function render()
    {
        $activities = $this->loadActivities();
        $kanbanColumns = $this->buildKanbanColumns($activities);

        return view('livewire.dashboard.pending-activities-kanban', [
            'kanbanColumns' => $kanbanColumns,
            'totalCount' => $activities->count(),
        ]);
    }
}
