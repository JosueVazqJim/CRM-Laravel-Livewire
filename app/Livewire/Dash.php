<?php

namespace App\Livewire;

use App\Models\Companies\Company;
use App\Models\Leads\Lead;
use App\Models\Quotes\QuoteItem;
use App\Models\Quotes\QuoteVersion;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class Dash extends Component
{
    public ?int $selectedCompanyId = null;

    public array $companiesOptions = [];

    public string $adminViewMode = 'general';

    public ?int $viewAsUserId = null;

    public array $viewAsUserOptions = [];

    public array $usersOptions = [];

    public array $leadsByStageChart = [];

    public array $quotesStageByUserChart = [];

    public $switchUserPipeline = null;

    public array $salesPerMonthChart = [];

    public array $leadsByChannelChart = [];

    /** Ventana (días) para conversión, ticket promedio y ranking. */
    public const METRICS_WINDOW_DAYS = 90;

    /** Días sin cambios para considerar una cotización abierta como estancada. */
    public const STALE_QUOTE_DAYS = 14;

    /** Estados en los que una cotización sigue abierta. */
    private const OPEN_STATUSES = [QuoteVersion::STATUS_EN_PROCESO, QuoteVersion::STATUS_EN_VALIDACION];

    public bool $showNewLeadsModal = false;

    public string $newLeadsModalPeriod = 'week';

    public bool $showPipelineModal = false;

    public bool $showConversionModal = false;

    /** Filtro del modal de conversión: all | exitosa | sin_exito */
    public string $conversionModalFilter = 'all';

    public bool $showOpenQuotesModal = false;

    // ─── Lifecycle hooks ────────────────────────────────────────────────────

    public function updatedSwitchUserPipeline($id): void
    {
        if ($this->adminViewMode !== 'general') {
            return;
        }

        Arr::set($this->quotesStageByUserChart, 'data.labels',
            $this->loadQuotesStageByUser()->keys()->toArray());
        Arr::set($this->quotesStageByUserChart, 'data.datasets.0.data',
            $this->loadQuotesStageByUser()->values()->toArray());
    }

    public function updatedSelectedCompanyId(): void
    {
        $user = auth()->user();

        $this->adminViewMode = 'general';
        $this->viewAsUserId = null;

        if ($user->hasRole('admin') && $this->selectedCompanyId) {
            $company = Company::find($this->selectedCompanyId);

            $this->usersOptions = $company->users()
                ->where('active', true)
                ->get()
                ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])
                ->toArray();

            $this->switchUserPipeline = $this->usersOptions[0]['id'] ?? auth()->id();

            $this->viewAsUserOptions = User::where('active', true)
                ->whereHas('companies', fn ($q) => $q->where('companies.id', $this->selectedCompanyId)
                )
                ->get()
                ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])
                ->toArray();
        }

        $this->reloadAllCharts();
    }

    public function updatedViewAsUserId(): void
    {
        $this->adminViewMode = 'user';
        $this->reloadAllCharts();
    }

    // ─── Acciones públicas ──────────────────────────────────────────────────

    public function setViewMode(string $mode): void
    {
        $this->adminViewMode = $mode;

        if ($mode === 'user' && ! $this->viewAsUserId) {
            $this->viewAsUserId = $this->viewAsUserOptions[0]['id'] ?? auth()->id();
        }

        $this->reloadAllCharts();
    }

    // ─── Helpers privados ───────────────────────────────────────────────────

    private function getEffectiveUserId(): ?int
    {
        if (! auth()->user()->hasRole('admin')) {
            return auth()->id();
        }

        return match ($this->adminViewMode) {
            'mine' => auth()->id(),
            'user' => $this->viewAsUserId,
            'general' => null,
        };
    }

    private function reloadAllCharts(): void
    {
        Arr::set($this->leadsByStageChart, 'data.labels',
            $this->loadLeadsByStage()->keys()->toArray());
        Arr::set($this->leadsByStageChart, 'data.datasets.0.data',
            $this->loadLeadsByStage()->values()->toArray());

        Arr::set($this->quotesStageByUserChart, 'data.labels',
            $this->loadQuotesStageByUser()->keys()->toArray());
        Arr::set($this->quotesStageByUserChart, 'data.datasets.0.data',
            $this->loadQuotesStageByUser()->values()->toArray());

        Arr::set($this->salesPerMonthChart, 'data.labels',
            $this->loadSalesPerMonth()->pluck('mes')->toArray());
        Arr::set($this->salesPerMonthChart, 'data.datasets.0.data',
            $this->loadSalesPerMonth()->pluck('total_mes')->toArray());

        $leadsByChannel = $this->loadLeadsByChannel();
        Arr::set($this->leadsByChannelChart, 'data.labels', $leadsByChannel->keys()->toArray());
        Arr::set($this->leadsByChannelChart, 'data.datasets.0.data', $leadsByChannel->values()->toArray());
    }

    /**
     * Aplica los filtros del dashboard (empresa y usuario efectivo) sobre la relación `quote`.
     */
    private function applyQuoteScope(Builder $quoteQuery): void
    {
        $effectiveUserId = $this->getEffectiveUserId();

        if ($this->selectedCompanyId) {
            $quoteQuery->where('company_id', $this->selectedCompanyId);
        }

        if ($effectiveUserId) {
            $quoteQuery->where('user_id', $effectiveUserId);
        }
    }

    /**
     * Versiones de cotización visibles con los filtros actuales del dashboard.
     */
    private function scopedVersions(): Builder
    {
        return QuoteVersion::query()
            ->whereHas('quote', fn (Builder $q) => $this->applyQuoteScope($q));
    }

    /**
     * Versiones abiertas y vigentes: sin una revisión posterior que las reemplace,
     * para no contar varias veces el mismo expediente.
     */
    private function openCurrentVersions(): Builder
    {
        return $this->scopedVersions()
            ->whereIn('status', self::OPEN_STATUSES)
            ->whereDoesntHave('childVersionQuotes');
    }

    /**
     * Versiones ganadas o perdidas en los últimos METRICS_WINDOW_DAYS días (base de la tasa de conversión).
     */
    private function closedVersionsInWindow(): Builder
    {
        return $this->scopedVersions()
            ->whereIn('status', [QuoteVersion::STATUS_EXITOSA, QuoteVersion::STATUS_SIN_EXITO])
            ->where('updated_at', '>=', now()->subDays(self::METRICS_WINDOW_DAYS));
    }

    private function loadLeadsByChannel(): Collection
    {
        $effectiveUserId = $this->getEffectiveUserId();

        return Lead::query()
            ->when($this->selectedCompanyId, fn (Builder $q) => $q->where('company_id', $this->selectedCompanyId))
            ->when($effectiveUserId, fn (Builder $q) => $q->where(function (Builder $q) use ($effectiveUserId): void {
                $q->where('user_id', $effectiveUserId)
                    ->orWhereHas('userAccess', fn (Builder $qa) => $qa->where('user_id', $effectiveUserId));
            }))
            ->selectRaw('undermine_channel, COUNT(*) as total')
            ->groupBy('undermine_channel')
            ->orderByDesc('total')
            ->pluck('total', 'undermine_channel')
            ->mapWithKeys(fn (int $total, ?string $channel): array => [
                filled($channel) ? ucfirst($channel) : 'Sin canal' => $total,
            ]);
    }

    // ─── Métricas adicionales ───────────────────────────────────────────────

    /**
     * Tasa de conversión y ticket promedio de los últimos METRICS_WINDOW_DAYS días.
     *
     * @return array{won: int, lost: int, rate: float|null, avg_ticket: float}
     */
    #[Computed]
    public function conversionStats(): array
    {
        $closed = $this->closedVersionsInWindow()
            ->selectRaw('status, COUNT(*) as cnt, AVG(total) as avg_total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $won = (int) ($closed[QuoteVersion::STATUS_EXITOSA]->cnt ?? 0);
        $lost = (int) ($closed[QuoteVersion::STATUS_SIN_EXITO]->cnt ?? 0);

        return [
            'won' => $won,
            'lost' => $lost,
            'rate' => ($won + $lost) > 0 ? round($won / ($won + $lost) * 100, 1) : null,
            'avg_ticket' => round((float) ($closed[QuoteVersion::STATUS_EXITOSA]->avg_total ?? 0), 2),
        ];
    }

    /**
     * @return array{count: int, amount: float}
     */
    #[Computed]
    public function openQuotesStats(): array
    {
        $stats = $this->openCurrentVersions()
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(total), 0) as amount')
            ->first();

        return [
            'count' => (int) $stats->cnt,
            'amount' => round((float) $stats->amount, 2),
        ];
    }

    /**
     * Productos con más ingreso en cotizaciones exitosas (precio con descuento, en USD).
     */
    #[Computed]
    public function topProducts(): Collection
    {
        return QuoteItem::query()
            ->whereNotNull('product_id')
            ->whereHas('quoteVersion', fn (Builder $v) => $v
                ->where('status', QuoteVersion::STATUS_EXITOSA)
                ->whereHas('quote', fn (Builder $q) => $this->applyQuoteScope($q)))
            ->selectRaw('product_id,
                SUM(quantity) as units,
                COUNT(DISTINCT quote_version_id) as quotes_count,
                SUM(quantity * unit_price_converted * (1 - discount / 100)) as revenue')
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->limit(5)
            ->with('product:id,name,sku')
            ->get();
    }

    /**
     * Cotizaciones abiertas sin cambios en los últimos STALE_QUOTE_DAYS días.
     */
    #[Computed]
    public function staleQuotes(): Collection
    {
        return $this->openCurrentVersions()
            ->where('updated_at', '<', now()->subDays(self::STALE_QUOTE_DAYS))
            ->with(['quote:id,lead_id,user_id', 'quote.lead:id,name_company', 'quote.user:id,first_name,paternal_surname,maternal_surname'])
            ->orderBy('updated_at')
            ->limit(6)
            ->get();
    }

    /**
     * Ranking de vendedores (solo admin en vista general).
     */
    #[Computed]
    public function sellerRanking(): Collection
    {
        if (! auth()->user()->hasRole('admin') || $this->adminViewMode !== 'general') {
            return collect();
        }

        $rows = QuoteVersion::query()
            ->join('quotes', 'quotes.id', '=', 'quote_versions.quote_id')
            ->when($this->selectedCompanyId, fn (Builder $q) => $q->where('quotes.company_id', $this->selectedCompanyId))
            ->whereIn('quote_versions.status', [QuoteVersion::STATUS_EXITOSA, QuoteVersion::STATUS_SIN_EXITO])
            ->where('quote_versions.updated_at', '>=', now()->subDays(self::METRICS_WINDOW_DAYS))
            ->selectRaw("quotes.user_id,
                SUM(CASE WHEN quote_versions.status = 'exitosa' THEN 1 ELSE 0 END) as won,
                SUM(CASE WHEN quote_versions.status = 'sin_exito' THEN 1 ELSE 0 END) as lost,
                SUM(CASE WHEN quote_versions.status = 'exitosa' THEN quote_versions.total ELSE 0 END) as amount")
            ->groupBy('quotes.user_id')
            ->orderByDesc('amount')
            ->limit(8)
            ->get();

        $users = User::query()
            ->whereIn('id', $rows->pluck('user_id'))
            ->get(['id', 'first_name', 'paternal_surname', 'maternal_surname', 'avatar'])
            ->keyBy('id');

        return $rows->map(fn ($row): array => [
            'user' => $users->get($row->user_id),
            'won' => (int) $row->won,
            'lost' => (int) $row->lost,
            'amount' => round((float) $row->amount, 2),
            'rate' => ($row->won + $row->lost) > 0 ? round($row->won / ($row->won + $row->lost) * 100) : 0,
        ]);
    }

    // ─── Carga de datos ─────────────────────────────────────────────────────

    private function loadLeadsByStage()
    {
        $effectiveUserId = $this->getEffectiveUserId();
        $query = Lead::query();

        if ($this->selectedCompanyId) {
            $query->whereHas('company', fn ($q) => $q->where('companies.id', $this->selectedCompanyId)
            );
        }

        if ($effectiveUserId) {
            $query->where(function ($q) use ($effectiveUserId) {
                $q->where('user_id', $effectiveUserId)
                    ->orWhereHas('userAccess', fn ($qa) => $qa->where('user_id', $effectiveUserId)
                    );
            });
        }

        return $query->get()->groupBy('funnel_stage')
            ->map(fn ($leads) => $leads->count());
    }

    private function loadQuotesStageByUser(): object
    {
        $effectiveUserId = $this->getEffectiveUserId();

        // En modo general el selector switchUserPipeline controla este chart
        if ($this->adminViewMode === 'general' && $this->switchUserPipeline) {
            $effectiveUserId = $this->switchUserPipeline;
        }

        $query = QuoteVersion::whereHas('quote', function ($q) use ($effectiveUserId) {
            if ($this->selectedCompanyId) {
                $q->where('company_id', $this->selectedCompanyId);
            }
            if ($effectiveUserId) {
                $q->where('user_id', $effectiveUserId);
            }
        });

        return $query->groupBy('status')
            ->selectRaw('status, COUNT(*) as total')
            ->pluck('total', 'status');
    }

    /**
     * Ventas exitosas de los últimos 6 meses (incluido el actual), agrupadas por mes.
     *
     * La agrupación se hace en PHP para no depender de funciones de fecha propias
     * de cada motor (DATE_FORMAT en MySQL, strftime en SQLite). Los meses sin
     * ventas aparecen con 0 para que la gráfica no tenga huecos.
     *
     * @return Collection<int, array{mes: string, total_mes: int}>
     */
    private function loadSalesPerMonth(): Collection
    {
        $from = now()->subMonths(5)->startOfMonth();

        $totalsByMonth = $this->scopedVersions()
            ->where('status', QuoteVersion::STATUS_EXITOSA)
            ->where('closed_at', '>=', $from)
            ->get(['closed_at', 'total'])
            ->groupBy(fn (QuoteVersion $version): string => $version->closed_at->format('Y-m'))
            ->map(fn (Collection $versions): int => (int) round($versions->sum('total')));

        return collect(range(0, 5))->map(function (int $offset) use ($from, $totalsByMonth): array {
            $month = $from->copy()->addMonths($offset)->format('Y-m');

            return ['mes' => $month, 'total_mes' => $totalsByMonth->get($month, 0)];
        });
    }

    private function buildPipelineThisMonthQuery()
    {
        $effectiveUserId = $this->getEffectiveUserId();

        $query = QuoteVersion::where('closed_at', '>=', now()->startOfMonth())
            ->where('status', 'exitosa');

        if ($this->selectedCompanyId) {
            $query->whereHas('quote', fn ($q) => $q->where('company_id', $this->selectedCompanyId)
            );
        }

        if ($effectiveUserId) {
            $query->whereHas('quote', fn ($q) => $q->where('user_id', $effectiveUserId)
            );
        }

        return $query;
    }

    private function totalPipeLineThisMonth()
    {
        return round($this->buildPipelineThisMonthQuery()->sum('total'), 2);
    }

    private function porcentajePipeLineLastMonth()
    {
        $effectiveUserId = $this->getEffectiveUserId();

        $lastMonthQuery = QuoteVersion::whereBetween('closed_at', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth(),
        ])->where('status', 'exitosa');

        if ($this->selectedCompanyId) {
            $lastMonthQuery->whereHas('quote', fn ($q) => $q->where('company_id', $this->selectedCompanyId)
            );
        }

        if ($effectiveUserId) {
            $lastMonthQuery->whereHas('quote', fn ($q) => $q->where('user_id', $effectiveUserId)
            );
        }

        $lastMonth = $lastMonthQuery->sum('total');
        $thisMonth = $this->totalPipeLineThisMonth();

        if ($lastMonth <= 0) {
            return 100;
        }

        return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 2);
    }

    private function buildNewLeadsQuery(string $period)
    {
        $user = auth()->user();
        $query = Lead::query();

        if ($period === 'week') {
            $query->whereDate('creat_at', '>=', now()->startOfWeek());
        } else {
            $query->whereDate('creat_at', today());
        }

        if ($this->selectedCompanyId) {
            $query->where('company_id', $this->selectedCompanyId);
        }

        if ($user->hasRole('admin')) {
            if ($this->adminViewMode === 'general') {
                $metaUserId = User::where('first_name', 'system_meta')->value('id');
                if ($metaUserId) {
                    $query->where('user_id', $metaUserId);
                } else {
                    $query->whereRaw('0 = 1');
                }
            } elseif ($this->adminViewMode === 'mine') {
                $query->where(function ($q) {
                    $q->where('user_id', auth()->id())
                        ->orWhereHas('userAccess', fn ($qa) => $qa->where('user_id', auth()->id())
                        );
                });
            } elseif ($this->adminViewMode === 'user' && $this->viewAsUserId) {
                $query->where(function ($q) {
                    $q->where('user_id', $this->viewAsUserId)
                        ->orWhereHas('userAccess', fn ($qa) => $qa->where('user_id', $this->viewAsUserId)
                        );
                });
            }
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('userAccess', fn ($qa) => $qa->where('user_id', $user->id)
                    );
            });
        }

        return $query;
    }

    private function countLeadsThisWeek(): int
    {
        return $this->buildNewLeadsQuery('week')->count();
    }

    private function countLeadsToday(): int
    {
        return $this->buildNewLeadsQuery('today')->count();
    }

    // ─── Modal: leads nuevos ────────────────────────────────────────────────

    public function openNewLeadsModal(string $period): void
    {
        $this->newLeadsModalPeriod = $period;
        $this->showNewLeadsModal = true;
    }

    public function newLeadsModalList()
    {
        return $this->buildNewLeadsQuery($this->newLeadsModalPeriod)
            ->with([
                'user:id,first_name,paternal_surname,avatar',
                'company:id,name,avatar',
            ])
            ->orderByDesc('creat_at')
            ->get();
    }

    // ─── Modal: pipeline del mes ────────────────────────────────────────────

    public function openPipelineModal(): void
    {
        $this->showPipelineModal = true;
    }

    public function pipelineModalList()
    {
        return $this->buildPipelineThisMonthQuery()
            ->with(['quote.user', 'quote.lead', 'quote.company'])
            ->orderByDesc('closed_at')
            ->get();
    }

    // ─── Modal: tasa de conversión ──────────────────────────────────────────

    public function openConversionModal(string $filter = 'all'): void
    {
        $this->conversionModalFilter = in_array($filter, [QuoteVersion::STATUS_EXITOSA, QuoteVersion::STATUS_SIN_EXITO], true)
            ? $filter
            : 'all';
        $this->showConversionModal = true;
    }

    public function conversionModalList(): Collection
    {
        return $this->closedVersionsInWindow()
            ->when($this->conversionModalFilter !== 'all', fn (Builder $q) => $q->where('status', $this->conversionModalFilter))
            ->with(['quote.user', 'quote.lead', 'quote.company'])
            ->orderByDesc('updated_at')
            ->get();
    }

    // ─── Modal: cotizaciones abiertas ───────────────────────────────────────

    public function openOpenQuotesModal(): void
    {
        $this->showOpenQuotesModal = true;
    }

    public function openQuotesModalList(): Collection
    {
        return $this->openCurrentVersions()
            ->with(['quote.user', 'quote.lead', 'quote.company'])
            ->orderBy('updated_at')
            ->get();
    }

    #[Computed]
    public function selectedCompany(): ?Company
    {
        return $this->selectedCompanyId
            ? Company::find($this->selectedCompanyId)
            : null;
    }

    // ─── Mount ──────────────────────────────────────────────────────────────

    public function mount()
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            $companies = Company::where('active', true)->get();
        } else {
            $companies = $user->companies()->where('active', true)->get();
        }

        $this->companiesOptions = $companies->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
        ])->toArray();

        $this->selectedCompanyId = $companies->first()?->id;

        if ($user->hasRole('admin')) {
            $this->usersOptions = User::where('active', true)
                ->get()
                ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])
                ->toArray();

            $this->switchUserPipeline = $this->usersOptions[0]['id'] ?? auth()->id();

            if ($this->selectedCompanyId) {
                $this->viewAsUserOptions = User::where('active', true)
                    ->whereHas('companies', fn ($q) => $q->where('companies.id', $this->selectedCompanyId)
                    )
                    ->get()
                    ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])
                    ->toArray();
            }
        } else {
            $this->usersOptions = [[
                'id' => $user->id,
                'name' => $user->name,
            ]];
            $this->switchUserPipeline = $user->id;
        }

        $this->leadsByStageChart = [
            'type' => 'bar',
            'data' => [
                'labels' => [],
                'datasets' => [[
                    'label' => 'Número de Leads',
                    'data' => [],
                    'backgroundColor' => ['rgba(54, 162, 235, 0.2)'],
                    'borderColor' => ['rgb(54, 162, 235)'],
                    'borderWidth' => '1',
                ]],
            ],
            'options' => ['responsive' => true],
        ];

        $this->quotesStageByUserChart = [
            'type' => 'bar',
            'data' => [
                'labels' => [],
                'datasets' => [[
                    'label' => 'Leads',
                    'data' => [],
                    'backgroundColor' => ['rgba(153, 102, 255, 0.2)'],
                    'borderColor' => ['rgb(153, 102, 255)'],
                    'borderWidth' => '1',
                ]],
            ],
            'options' => ['responsive' => true, 'indexAxis' => 'y'],
        ];

        $this->salesPerMonthChart = [
            'type' => 'line',
            'data' => [
                'labels' => [],
                'datasets' => [[
                    'label' => 'Ventas por mes',
                    'data' => [],
                    'backgroundColor' => ['rgba(75, 192, 192, 0.2)'],
                    'borderColor' => ['rgb(75, 192, 192)'],
                    'borderWidth' => '1',
                ]],
            ],
            'options' => ['responsive' => true],
        ];

        $this->leadsByChannelChart = [
            'type' => 'doughnut',
            'data' => [
                'labels' => [],
                'datasets' => [[
                    'label' => 'Leads',
                    'data' => [],
                    'backgroundColor' => [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(201, 203, 207, 0.7)',
                    ],
                    'borderWidth' => 0,
                ]],
            ],
            'options' => [
                'responsive' => true,
                'plugins' => ['legend' => ['position' => 'bottom']],
            ],
        ];

        $this->reloadAllCharts();
    }

    public function render()
    {
        return view('livewire.dash');
    }
}
