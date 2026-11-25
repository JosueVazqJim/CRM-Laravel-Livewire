<?php

namespace App\Livewire\Leads\ComponentsShow;

use App\Models\Leads\Lead;
use App\Models\Quotes\QuoteVersion;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class QuotesIndex extends Component
{
    use Toast, WithPagination;

    public Lead $lead;

    // Filtros
    public string $search = '';

    public string $statusFilter = '';

    public string $versionType = 'all'; // all, same_rate, new_rate

    public string $sortBy = 'created_at_desc';

    // Filtro por usuario: 'mine' | '<user_id>'
    public string $userFilter = 'mine';

    public array $userFilterOptions = [];

    // Propiedades para el modal de detalles
    public bool $showDetailsModal = false;

    public ?QuoteVersion $selectedQuote = null;

    public array $statusOptions = [
        ['id' => 'en_proceso', 'name' => 'En Proceso'],
        ['id' => 'en_validacion', 'name' => 'En Validación'],
        ['id' => 'exitosa', 'name' => 'Exitosa'],
        ['id' => 'sin_exito', 'name' => 'Sin Éxito'],
        ['id' => 'cancelada', 'name' => 'Cancelada'],
        ['id' => 'papelera', 'name' => 'Papelera'],
    ];

    public array $versionOptions = [
        ['id' => 'all', 'name' => 'Todas las versiones'],
        ['id' => 'same_rate', 'name' => 'Misma tasa de cambio'],
        ['id' => 'new_rate', 'name' => 'Recotizaciones (nueva tasa)'],
    ];

    public array $sortByOptions = [
        ['id' => 'created_at_desc', 'name' => 'Más reciente'],
        ['id' => 'created_at_asc', 'name' => 'Más antigua'],
        ['id' => 'folio_asc', 'name' => 'Folio (A-Z)'],
        ['id' => 'folio_desc', 'name' => 'Folio (Z-A)'],
        ['id' => 'total_desc', 'name' => 'Mayor monto'],
        ['id' => 'total_asc', 'name' => 'Menor monto'],
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'versionType' => ['except' => 'all'],
        'sortBy' => ['except' => 'created_at_desc'],
        'userFilter' => ['except' => 'mine'],
    ];

    public function mount(Lead $lead): void
    {
        $this->lead = $lead;

        $this->userFilterOptions = collect([
            ['id' => 'all', 'name' => 'Todas'],
            ['id' => 'mine', 'name' => 'Mis cotizaciones'],
        ])->concat(
            User::where('active', true)
                ->where('id', '!=', auth()->id())
                ->whereHas('companies', fn ($q) => $q->where('companies.id', $lead->company_id))
                ->orderBy('first_name')
                ->get()
                ->map(fn ($u) => ['id' => (string) $u->id, 'name' => $u->name])
        )->toArray();
    }

    /**
     * Restringe la consulta al usuario elegido en el filtro.
     * Cualquier usuario con acceso al lead puede ver las cotizaciones
     * de cualquier otro usuario para ese lead.
     */
    private function applyUserFilter(Builder $query): void
    {
        if ($this->userFilter === 'mine') {
            $query->where('user_id', auth()->id());
        } elseif (is_numeric($this->userFilter)) {
            $query->where('user_id', (int) $this->userFilter);
        }
    }

    public function updatedUserFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Obtener las cotizaciones con filtros aplicados
     */
    public function getQuotesProperty()
    {

        $query = QuoteVersion::query()
            ->with([
                'quote',
                'quote.user',
                'quote.company',
                'items.product',
                'exchangeRates',
            ])
            ->whereHas('quote', function (Builder $q) {
                $q->where('lead_id', $this->lead->id);
                $this->applyUserFilter($q);
            });

        // Búsqueda por folio
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('folio', 'like', '%'.$this->search.'%')
                    ->orWhereHas('quote.company', function ($q2) {
                        $q2->where('name', 'like', '%'.$this->search.'%');
                    });
            });
        }

        // Filtro por status
        if (! empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        // Filtro por tipo de versión
        if ($this->versionType === 'same_rate') {
            // Versiones que NO son recotizaciones (source_version_id es null)
            $query->whereNull('source_version_id');
        } elseif ($this->versionType === 'new_rate') {
            // Versiones que SÍ son recotizaciones
            $query->whereNotNull('source_version_id');
        }

        // Ordenamiento
        switch ($this->sortBy) {
            case 'folio_asc':
                $query->orderBy('folio', 'asc');
                break;
            case 'folio_desc':
                $query->orderBy('folio', 'desc');
                break;
            case 'total_asc':
                $query->orderBy('total', 'asc');
                break;
            case 'total_desc':
                $query->orderBy('total', 'desc');
                break;
            case 'created_at_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'created_at_desc':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query->paginate(10);
    }

    /**
     * Obtener estadísticas para el resumen
     */
    public function getStatsProperty()
    {

        $baseQuery = QuoteVersion::query()
            ->whereHas('quote', function (Builder $q) {
                $q->where('lead_id', $this->lead->id);
                $this->applyUserFilter($q);
            });

        return [
            'total' => $baseQuery->count(),
            'total_in_process' => (clone $baseQuery)->where('status', 'en_proceso')->count(),
            'total_in_validation' => (clone $baseQuery)->where('status', 'en_validacion')->count(),
            'total_approved' => (clone $baseQuery)->where('status', 'exitosa')->count(),
            'total_rejected' => (clone $baseQuery)->where('status', 'sin_exito')->count(),
            'total_cancelled' => (clone $baseQuery)->where('status', 'cancelada')->count(),
            'total_amount' => (clone $baseQuery)->where('status', 'exitosa')->sum('total'),
            'versions_count' => $baseQuery->count(),
            'recotizations_count' => $baseQuery->whereNotNull('source_version_id')->count(),
        ];
    }

    /**
     * Cambiar estado de una cotización
     */
    public function changeStatus(QuoteVersion $quote, string $newStatus)
    {
        $allowedTransitions = [
            'en_proceso' => ['en_validacion', 'exitosa', 'sin_exito', 'cancelada', 'papelera'],
            'en_validacion' => ['en_proceso', 'exitosa', 'sin_exito', 'cancelada', 'papelera'],
            'exitosa' => ['en_proceso', 'en_validacion', 'sin_exito', 'cancelada', 'papelera'],
            'sin_exito' => ['en_proceso', 'exitosa', 'en_validacion', 'cancelada', 'papelera'],
            'cancelada' => ['en_proceso', 'exitosa', 'sin_exito', 'en_validacion', 'papelera'],
            'papelera' => ['en_proceso', 'exitosa', 'sin_exito', 'cancelada', 'en_validacion'],
        ];

        if (! in_array($newStatus, $allowedTransitions[$quote->status] ?? [])) {
            $this->error('No puedes cambiar de '.$quote->status.' a '.$newStatus);

            return;
        }

        $quote->update(['status' => $newStatus]);

        $this->success('Estado actualizado correctamente');
        $this->dispatch('quote-status-updated');
    }

    /**
     * Obtener badge de estado
     */
    public function getStatusBadge(string $status): array
    {
        return match ($status) {
            'en_proceso' => ['label' => 'En Proceso', 'class' => 'badge-info'],
            'en_validacion' => ['label' => 'En Validación', 'class' => 'badge-warning'],
            'exitosa' => ['label' => 'Exitosa', 'class' => 'badge-success'],
            'sin_exito' => ['label' => 'Sin Éxito', 'class' => 'badge-error'],
            'cancelada' => ['label' => 'Cancelada', 'class' => 'badge-neutral'],
            'papelera' => ['label' => 'Papelera', 'class' => 'badge-ghost'],
            default => ['label' => $status, 'class' => 'badge-ghost'],
        };
    }

    /**
     * Resetear filtros
     */
    public function resetFilters()
    {
        $this->reset(['search', 'statusFilter', 'versionType', 'sortBy']);
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.leads.components-show.quotes-index', [
            'quotes' => $this->quotes,
            'stats' => $this->stats,
        ]);
    }
}
