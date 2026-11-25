<?php

namespace App\Livewire\Leads;

use App\Livewire\Traits\ClearsProperties;
use App\Models\Companies\Company;
use App\Models\Leads\Lead;
use App\Models\States\State;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Leads')]
class Index extends Component
{
    use ClearsProperties, WithPagination;

    // Propiedades de búsqueda global
    public string $search = '';

    // Propiedades de filtro por empresa
    public int $company = 0;

    public array $companyOptions = [];

    // Propiedades de filtros específicos
    public string $nameCompany = '';

    public string|int $stateId = '';

    public array $stateOptions = [];

    public string $urgencyLevel = '';

    public string $classification = '';

    public string $funnelStage = '';

    public string $clientProgress = '';

    public string $payStatus = '';

    public string $afterSales = '';

    public string $acquisitionChannel = '';

    public int $probability = 0;

    public int $probabilityRange = 0;

    public string $clientStatus = '';

    public array $users = [];

    // Filtro por usuario (solo admin, incluye usuarios activos e inactivos)
    public string|int $userId = '';

    public array $userOptions = [];

    // Control de UI
    public array $sortBy = ['column' => 'creat_at', 'direction' => 'desc'];

    public bool $showFilters = false;

    public bool $showMetaLeads = false;

    public bool $showMyLeads = false;

    public bool $showSharedLeads = false;

    // Paginación
    public int $perPage = 15;

    public const PER_PAGE_OPTIONS = [15, 25, 50, 100];

    public function mount(): void
    {
        // Obtener empresas asignadas al usuario autenticado
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            // Admin ve todas las empresas
            $companies = Company::all();
        } else {
            // Vendedor solo ve sus empresas asignadas
            $companies = $user->companies()->where('active', true)->get();
        }

        $this->companyOptions = $companies->map(fn ($company) => [
            'id' => $company->id,
            'name' => $company->name,
            'avatar' => $company->avatar_url,
        ])->toArray();

        // Establecer la primera empresa como predeterminada
        if (! empty($this->companyOptions)) {
            $this->company = $this->companyOptions[0]['id'];
        }

        $this->stateOptions = State::orderBy('name')->get(['id', 'name'])->toArray();

        if ($user->hasRole('admin')) {
            $this->userOptions = User::query()
                ->orderByDesc('active')
                ->orderBy('first_name')
                ->orderBy('paternal_surname')
                ->get(['id', 'first_name', 'paternal_surname', 'maternal_surname', 'active'])
                ->map(fn (User $u) => [
                    'id' => $u->id,
                    'name' => $u->full_name.($u->active ? '' : ' (Inactivo)'),
                ])
                ->toArray();
        }
    }

    // IMPORTANTE: Resetear paginación cuando cambian los filtros
    public function updated($property): void
    {
        if (in_array($property, [
            'search',
            'company',
            'nameCompany',
            'stateId',
            'urgencyLevel',
            'classification',
            'funnelStage',
            'clientProgress',
            'payStatus',
            'afterSales',
            'probabilityRange',
            'clientStatus',
            'acquisitionChannel',
            'userId',
            'showMetaLeads',
            'showMyLeads',
            'showSharedLeads',
            'perPage',
        ])) {
            $this->resetPage();
        }
    }

    public function updatedPerPage(): void
    {
        if (! in_array($this->perPage, self::PER_PAGE_OPTIONS, true)) {
            $this->perPage = self::PER_PAGE_OPTIONS[0];
        }
    }

    // Método para contar filtros activos
    public function getFilterCountProperty(): int
    {
        $count = 0;

        if (! empty($this->nameCompany)) {
            $count++;
        }
        if (! empty($this->stateId)) {
            $count++;
        }
        if (! empty($this->urgencyLevel)) {
            $count++;
        }
        if (! empty($this->classification)) {
            $count++;
        }
        if (! empty($this->funnelStage)) {
            $count++;
        }
        if (! empty($this->clientProgress)) {
            $count++;
        }
        if (! empty($this->payStatus)) {
            $count++;
        }
        if (! empty($this->afterSales)) {
            $count++;
        }
        if (! empty($this->probabilityRange)) {
            $count++;
        }
        if (! empty($this->clientStatus)) {
            $count++;
        }
        if (! empty($this->acquisitionChannel)) {
            $count++;
        }
        if (! empty($this->userId) && auth()->user()->hasRole('admin')) {
            $count++;
        }

        return $count;
    }

    // Método principal que obtiene los leads con filtros
    public function getLeadsProperty(): LengthAwarePaginator
    {
        $user = auth()->user();
        $query = Lead::query();

        // FILTRO POR EMPRESA (siempre aplicado)
        if ($this->company) {
            $query->where('company_id', $this->company);
        }

        // FILTRO META LEADS
        if ($this->showMetaLeads) {
            $metaUserId = $this->getMetaUserId();
            if ($metaUserId) {
                $query->where('user_id', $metaUserId)
                    ->whereNotExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('lead_user_access')
                            ->whereColumn('lead_user_access.lead_id', 'leads.id');
                    });
            }
        } elseif ($this->showMyLeads) {
            // Leads creados por el usuario y los compartidos con él
            $this->applyOwnedOrSharedScope($query, $user->id);
        } elseif ($this->showSharedLeads) {
            // Solo leads compartidos con el usuario
            $query->whereExists(function ($subquery) use ($user) {
                $subquery->select(DB::raw(1))
                    ->from('lead_user_access')
                    ->whereColumn('lead_user_access.lead_id', 'leads.id')
                    ->where('lead_user_access.user_id', $user->id);
            });
        } else {
            // CONTROL DE ACCESO POR ROL
            if ($user->hasRole('admin')) {
                // Admin sin filtro: ve todos los leads de la company
            } else {
                // Vendedor: solo sus leads o los que tiene acceso
                $this->applyOwnedOrSharedScope($query, $user->id);
            }
        }

        // BÚSQUEDA GLOBAL - Busca en múltiples campos
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name_company', 'like', '%'.$this->search.'%')
                    ->orWhereHas('state', fn ($sq) => $sq->where('name', 'like', '%'.$this->search.'%'))
                    ->orWhere('classification', 'like', '%'.$this->search.'%')
                    ->orWhere('funnel_stage', 'like', '%'.$this->search.'%')
                    ->orWhereHas('contactPeople', fn ($sq) => $sq->where('name_contact', 'like', '%'.$this->search.'%'))
                    ->orWhereHas('contactPeople.channels', fn ($sq) => $sq->where('channel', 'like', '%'.$this->search.'%'));

                // Si el search parece un número, buscar por ID o probabilidad
                if (is_numeric($this->search)) {
                    $q->orWhere('id', $this->search);
                }
            });
        }

        // APLICAR FILTROS ESPECÍFICOS SOLO SI TIENEN VALOR
        if (! empty($this->nameCompany)) {
            $query->where('name_company', 'like', '%'.$this->nameCompany.'%');
        }

        if (! empty($this->stateId)) {
            $query->where('state_id', $this->stateId);
        }

        if (! empty($this->urgencyLevel)) {
            $query->where('urgency_level', 'like', '%'.$this->urgencyLevel.'%');
        }

        if (! empty($this->classification)) {
            $query->where('classification', 'like', '%'.$this->classification.'%');
        }

        if (! empty($this->funnelStage)) {
            $query->where('funnel_stage', 'like', '%'.$this->funnelStage.'%');
        }

        if (! empty($this->clientProgress)) {
            $query->where('client_progress', 'like', '%'.$this->clientProgress.'%');
        }

        if (! empty($this->payStatus)) {
            $query->where('pay_status', 'like', '%'.$this->payStatus.'%');
        }

        if (! empty($this->afterSales)) {
            $query->where('after_sales', 'like', '%'.$this->afterSales.'%');
        }

        // FILTRO DE PROBABILIDAD (ahora con range)
        if ($this->probabilityRange > 0) {
            $query->where('probability', '>=', $this->probabilityRange);
        }

        if (! empty($this->clientStatus)) {
            $query->where('client_status', 'like', '%'.$this->clientStatus.'%');
        }

        if (! empty($this->acquisitionChannel)) {
            $query->where('undermine_channel', 'like', '%'.$this->acquisitionChannel.'%');
        }

        // FILTRO POR USUARIO (solo admin): leads creados por el usuario, más los leads de
        // system_meta cuyo primer usuario compartido sea él
        if (! empty($this->userId) && $user->hasRole('admin')) {
            $this->applyCreatedByUserScope($query, (int) $this->userId);
        }

        // Eager loading para evitar N+1 queries
        $query->with([
            'state:id,name',
            'company:id,name,avatar',
            'user:id,first_name,paternal_surname,maternal_surname,avatar',
            'firstSharedAccess.user:id,first_name,paternal_surname,maternal_surname,avatar',
            'contactPeople:id,lead_id,name_contact',
        ]);

        // Aplicar ordenamiento
        return $query
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function headers(): array
    {
        return [
            ['key' => 'name_company', 'label' => 'Empresa Cliente', 'sortBy' => 'name_company'],
            ['key' => 'contact', 'label' => 'Contacto'],
            ['key' => 'user', 'label' => 'Creado por', 'sortBy' => 'user_id'],
            ['key' => 'state', 'label' => 'Estado', 'sortBy' => 'state_id'],
            ['key' => 'urgency_level', 'label' => 'Urgencia', 'sortBy' => 'urgency_level'],
            ['key' => 'classification', 'label' => 'Clasificación', 'sortBy' => 'classification'],
            ['key' => 'funnel_stage', 'label' => 'Embudo', 'sortBy' => 'funnel_stage'],
            ['key' => 'client_progress', 'label' => 'Fase de ventas', 'sortBy' => 'client_progress'],
            ['key' => 'probability', 'label' => 'Probabilidad', 'sortBy' => 'probability', 'class' => 'text-center'],
            ['key' => 'creat_at', 'label' => 'Creado', 'format' => ['date', 'd/m/Y H:i'], 'sortBy' => 'creat_at'],
        ];
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'leads' => $this->leads,
            'filterCount' => $this->filterCount,
            'companyOptions' => $this->companyOptions,
            'stateOptions' => $this->stateOptions,
        ];
    }

    protected function filters(): array
    {
        return [
            'nameCompany',
            'stateId',
            'urgencyLevel',
            'classification',
            'funnelStage',
            'clientProgress',
            'payStatus',
            'afterSales',
            'probabilityRange',
            'clientStatus',
            'acquisitionChannel',
            'userId',
        ];
    }

    public function toggleMyLeads(): void
    {
        $this->showMyLeads = ! $this->showMyLeads;
        if ($this->showMyLeads) {
            $this->showMetaLeads = false;
            $this->showSharedLeads = false;
        }
        $this->resetPage();
    }

    public function toggleMetaLeads(): void
    {
        $this->showMetaLeads = ! $this->showMetaLeads;
        if ($this->showMetaLeads) {
            $this->showMyLeads = false;
            $this->showSharedLeads = false;
        }
        $this->resetPage();
    }

    public function toggleSharedLeads(): void
    {
        $this->showSharedLeads = ! $this->showSharedLeads;
        if ($this->showSharedLeads) {
            $this->showMyLeads = false;
            $this->showMetaLeads = false;
        }
        $this->resetPage();
    }

    private function applyOwnedOrSharedScope(Builder $query, int $userId): void
    {
        $query->where(function ($q) use ($userId) {
            $q->where('leads.user_id', $userId)
                ->orWhereExists(function ($subquery) use ($userId) {
                    $subquery->select(DB::raw(1))
                        ->from('lead_user_access')
                        ->whereColumn('lead_user_access.lead_id', 'leads.id')
                        ->where('lead_user_access.user_id', $userId);
                });
        });
    }

    private function applyCreatedByUserScope(Builder $query, int $userId): void
    {
        $metaUserId = $this->getMetaUserId();

        $query->where(function ($q) use ($userId, $metaUserId) {
            $q->where('leads.user_id', $userId);

            if ($metaUserId) {
                $q->orWhere(function ($mq) use ($userId, $metaUserId) {
                    $mq->where('leads.user_id', $metaUserId)
                        ->where(function ($sub) {
                            // Primer usuario al que se compartió el lead (mismo criterio que firstSharedAccess)
                            $sub->select('lead_user_access.user_id')
                                ->from('lead_user_access')
                                ->whereColumn('lead_user_access.lead_id', 'leads.id')
                                ->orderBy('lead_user_access.id')
                                ->limit(1);
                        }, $userId);
                });
            }
        });
    }

    private function getMetaUserId(): ?int
    {
        return \App\Models\Users\User::where('first_name', 'system_meta')
            ->value('id');
    }

    public function render()
    {
        return view('livewire.leads.index');
    }
}
