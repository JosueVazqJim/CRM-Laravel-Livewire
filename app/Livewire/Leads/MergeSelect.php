<?php

namespace App\Livewire\Leads;

use App\Models\Leads\Lead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Fusionar lead')]
class MergeSelect extends Component
{
    use WithPagination;

    public Lead $lead;

    public string $search = '';

    /** @var int[] */
    public array $selectedLeadIds = [];

    public function mount(Lead $lead): void
    {
        $this->lead = $lead;

        $user = auth()->user();

        if (! $user->hasRole('admin')) {
            $isOwner = $lead->user_id === $user->id;
            $hasSharedAccess = $lead->userAccess()->where('user_id', $user->id)->exists();

            if (! $isOwner && ! $hasSharedAccess) {
                abort(403, 'No tienes permiso para acceder a este lead.');
            }
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function getCandidatesProperty(): LengthAwarePaginator
    {
        $user = auth()->user();

        $query = Lead::query()
            ->where('company_id', $this->lead->company_id)
            ->where('id', '!=', $this->lead->id);

        if (! $user->hasRole('admin')) {
            $query->where(function (Builder $q) use ($user) {
                $q->where('leads.user_id', $user->id)
                    ->orWhereExists(function ($subquery) use ($user) {
                        $subquery->select(DB::raw(1))
                            ->from('lead_user_access')
                            ->whereColumn('lead_user_access.lead_id', 'leads.id')
                            ->where('lead_user_access.user_id', $user->id);
                    });
            });
        }

        if (! empty($this->search)) {
            $query->where(function (Builder $q) {
                $q->where('name_company', 'like', '%'.$this->search.'%')
                    ->orWhereHas('state', fn ($sq) => $sq->where('name', 'like', '%'.$this->search.'%'))
                    ->orWhere('classification', 'like', '%'.$this->search.'%')
                    ->orWhere('funnel_stage', 'like', '%'.$this->search.'%')
                    ->orWhereHas('contactPeople', fn ($sq) => $sq->where('name_contact', 'like', '%'.$this->search.'%'))
                    ->orWhereHas('contactPeople.channels', fn ($sq) => $sq->where('channel', 'like', '%'.$this->search.'%'));

                if (is_numeric($this->search)) {
                    $q->orWhere('id', $this->search);
                }
            });
        }

        return $query
            ->with(['state:id,name', 'contactPeople:id,lead_id,name_contact'])
            ->orderByDesc('creat_at')
            ->paginate(10);
    }

    public function getMergeUrlProperty(): string
    {
        $ids = array_merge([$this->lead->id], $this->selectedLeadIds);

        return route('leads.merge').'?ids='.implode(',', $ids);
    }

    public function headers(): array
    {
        return [
            ['key' => 'select', 'label' => '', 'sortable' => false],
            ['key' => 'name_company', 'label' => 'Empresa Cliente'],
            ['key' => 'contact', 'label' => 'Contacto'],
            ['key' => 'state', 'label' => 'Estado'],
            ['key' => 'classification', 'label' => 'Clasificación'],
            ['key' => 'funnel_stage', 'label' => 'Embudo'],
            ['key' => 'creat_at', 'label' => 'Creado'],
        ];
    }

    public function render()
    {
        return view('livewire.leads.merge-select', [
            'headers' => $this->headers(),
            'candidates' => $this->candidates,
            'mergeUrl' => $this->mergeUrl,
        ]);
    }
}
