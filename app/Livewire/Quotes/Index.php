<?php

namespace App\Livewire\Quotes;

use App\Livewire\Traits\ClearsProperties;
use App\Models\Companies\Company;
use App\Models\Quotes\QuoteVersion;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Title('Cotizaciones')]
class Index extends Component
{
    use ClearsProperties, Toast, WithPagination;

    // Búsqueda global
    public string $search = '';

    // Filtro por empresa del corporativo
    public int $company = 0;

    public array $companyOptions = [];

    // Filtros del drawer
    public string $status = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    // Filtro por vendedor (solo admin, incluye usuarios activos e inactivos)
    public string|int $userId = '';

    public array $userOptions = [];

    // Control de UI
    public bool $showFilters = false;

    // Paginación
    public int $perPage = 10;

    public const PER_PAGE_OPTIONS = [10, 20, 30];

    public function mount(): void
    {
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

        // Por defecto se muestran las cotizaciones creadas por el usuario autenticado
        $this->userId = $user->id;
    }

    // Resetear paginación cuando cambian los filtros
    public function updated($property): void
    {
        if (in_array($property, [
            'search',
            'company',
            'status',
            'dateFrom',
            'dateTo',
            'userId',
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

    public function clear(): void
    {
        $this->reset($this->filters());
        $this->userId = auth()->id();
        $this->resetPage();
    }

    public function getFilterCountProperty(): int
    {
        $count = 0;

        if (! empty($this->status)) {
            $count++;
        }
        if (! empty($this->dateFrom) || ! empty($this->dateTo)) {
            $count++;
        }
        if (! empty($this->userId) && auth()->user()->hasRole('admin')) {
            $count++;
        }

        return $count;
    }

    public function getQuotesVersionsProperty(): LengthAwarePaginator
    {
        $user = auth()->user();
        $query = $this->scopedQuery();

        // FILTRO POR VENDEDOR (solo admin)
        if (! empty($this->userId) && $user->hasRole('admin')) {
            $query->whereHas('quote', fn ($q) => $q->where('user_id', $this->userId));
        }

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('folio', 'like', "%{$search}%")
                    ->orWhereHas('quote.lead', fn ($q) => $q->where('name_company', 'like', "%{$search}%"));
            });
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->dateFrom !== '') {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo !== '') {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query
            ->with(['quote.user', 'quote.lead', 'quote.company'])
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function statusOptions(): array
    {
        return [
            ['id' => QuoteVersion::STATUS_EN_PROCESO,    'name' => 'En Proceso'],
            ['id' => QuoteVersion::STATUS_EN_VALIDACION, 'name' => 'En Validación'],
            ['id' => QuoteVersion::STATUS_EXITOSA,       'name' => 'Exitosa'],
            ['id' => QuoteVersion::STATUS_SIN_EXITO,     'name' => 'Sin Éxito'],
            ['id' => QuoteVersion::STATUS_CANCELADA,     'name' => 'Cancelada'],
            ['id' => QuoteVersion::STATUS_PAPELERA,      'name' => 'Papelera'],
        ];
    }

    public function headers(): array
    {
        return [
            ['key' => 'folio',      'label' => 'Folio'],
            ['key' => 'lead',       'label' => 'Cliente'],
            ['key' => 'user',       'label' => 'Vendedor'],
            ['key' => 'status',     'label' => 'Estado'],
            ['key' => 'total',      'label' => 'Total USD'],
            ['key' => 'created_at', 'label' => 'Fecha'],
        ];
    }

    protected function filters(): array
    {
        return [
            'status',
            'dateFrom',
            'dateTo',
            'userId',
        ];
    }

    // Consulta base: empresa seleccionada + control de acceso por rol
    private function scopedQuery(): Builder
    {
        $user = auth()->user();

        return QuoteVersion::query()
            ->whereHas('quote', function ($q) use ($user) {
                $q->where('company_id', $this->company);

                // Vendedor: solo sus cotizaciones
                if (! $user->hasRole('admin')) {
                    $q->where('user_id', $user->id);
                }
            });
    }

    public function render()
    {
        return view('livewire.quotes.index', [
            'headers' => $this->headers(),
            'quotesVersions' => $this->quotesVersions,
            'statusOptions' => $this->statusOptions(),
            'filterCount' => $this->filterCount,
        ]);
    }
}
