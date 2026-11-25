<?php

namespace App\Livewire\Leads;

use App\Models\Leads\Lead;
use App\Models\Quotes\Quote;
use App\Services\Leads\LeadMergeService;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Fusionar leads')]
class Merge extends Component
{
    use Toast;

    /** @var int[] */
    public array $leadIds = [];

    public Collection $leads;

    public ?int $survivorId = null;

    /** @var array<string,int> */
    public array $fieldChoices = [];

    public ?int $billingLeadId = null;

    /** @var int[] */
    public array $contactPeopleIdsToKeep = [];

    public bool $confirming = false;

    public int $totalQuotes = 0;

    public int $totalActivities = 0;

    public int $totalNotes = 0;

    public int $totalUserAccess = 0;

    public int $totalContactPeople = 0;

    public function mount(): void
    {
        $ids = collect(explode(',', (string) request()->query('ids', '')))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->count() < 2) {
            abort(400, 'Selecciona al menos 2 leads para fusionar.');
        }

        $this->leadIds = $ids->all();

        $this->leads = Lead::whereIn('id', $this->leadIds)
            ->with(['state', 'company', 'user', 'billingInfo.address', 'contactPeople.channels', 'userAccess'])
            ->get();

        if ($this->leads->count() !== count($this->leadIds)) {
            abort(404, 'Alguno de los leads seleccionados no existe.');
        }

        if ($this->leads->pluck('company_id')->unique()->count() > 1) {
            abort(400, 'Todos los leads deben pertenecer a la misma empresa.');
        }

        $this->authorizeAccessToAllLeads();

        $survivor = $this->leads->first();
        $this->survivorId = $survivor->id;
        $this->billingLeadId = $survivor->id;

        foreach (array_keys($this->comparableFields()) as $field) {
            $this->fieldChoices[$field] = $survivor->id;
        }

        $this->contactPeopleIdsToKeep = $survivor->contactPeople->pluck('id')->all();

        $this->totalQuotes = Quote::whereIn('lead_id', $this->leadIds)->count();
        $this->totalActivities = $this->leads->sum(fn (Lead $lead) => $lead->activities()->count());
        $this->totalNotes = $this->leads->sum(fn (Lead $lead) => $lead->notes()->count());
        $this->totalUserAccess = $this->leads->sum(fn (Lead $lead) => $lead->userAccess->count());
        $this->totalContactPeople = $this->leads->sum(fn (Lead $lead) => $lead->contactPeople->count());
    }

    /**
     * Livewire recibe los value="" de los radios/checkboxes como string; sin
     * normalizar a int, las comparaciones estrictas en la vista y en el guard
     * del servicio de fusión (in_array(..., true)) fallan silenciosamente.
     */
    public function updatedSurvivorId(): void
    {
        $this->survivorId = (int) $this->survivorId;
    }

    public function updatedBillingLeadId(): void
    {
        $this->billingLeadId = (int) $this->billingLeadId;
    }

    public function updatedFieldChoices(mixed $value, string $key): void
    {
        $this->fieldChoices[$key] = (int) $value;
    }

    public function updatedContactPeopleIdsToKeep(): void
    {
        $this->contactPeopleIdsToKeep = array_map('intval', $this->contactPeopleIdsToKeep);
    }

    /**
     * Estado de un campo comparado entre leads: si todos los valores visibles
     * son iguales, si difieren (conflicto), o si ninguno tiene dato.
     *
     * @return array{0: string, 1: string} [etiqueta, clase de color]
     */
    public function fieldRowState(string $field): array
    {
        $values = $this->leads->map(fn (Lead $lead) => $this->displayValue($lead, $field));
        $filled = $values->reject(fn ($v) => $v === '—');

        if ($filled->isEmpty()) {
            return ['sin dato', 'text-base-content/40'];
        }

        if ($filled->unique()->count() === 1 && $filled->count() === $values->count()) {
            return ['idéntico', 'text-base-content/40'];
        }

        return ['conflicto', 'text-warning'];
    }

    /** Cantidad de campos en conflicto entre los leads seleccionados. */
    public function conflictCount(): int
    {
        return collect($this->comparableFields())
            ->keys()
            ->filter(fn (string $field) => $this->fieldRowState($field)[0] === 'conflicto')
            ->count();
    }

    /**
     * Cuántos valores (campos + facturación) provienen de cada lead, para el resumen final.
     *
     * @return array<int, array{lead: Lead, survives: bool, count: int}>
     */
    public function tallies(): array
    {
        return $this->leads->map(function (Lead $lead) {
            $fieldCount = collect($this->fieldChoices)->filter(fn ($id) => $id === $lead->id)->count();
            $billingCount = $this->billingLeadId === $lead->id ? 1 : 0;

            return [
                'lead' => $lead,
                'survives' => $this->survivorId === $lead->id,
                'count' => $fieldCount + $billingCount,
            ];
        })->all();
    }

    private function authorizeAccessToAllLeads(): void
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return;
        }

        foreach ($this->leads as $lead) {
            $isOwner = $lead->user_id === $user->id;
            $hasSharedAccess = $lead->userAccess->contains('user_id', $user->id);

            if (! $isOwner && ! $hasSharedAccess) {
                abort(403, 'No tienes permiso para fusionar uno o más de los leads seleccionados.');
            }
        }
    }

    /** @return array<string,string> */
    public function comparableFields(): array
    {
        return [
            'name_company' => 'Empresa cliente',
            'state_id' => 'Estado',
            'last_contact' => 'Último contacto',
            'creat_at' => 'Creado',
            'urgency_level' => 'Urgencia',
            'budget' => 'Presupuesto',
            'undermine_channel' => 'Canal de adquisición',
            'classification' => 'Clasificación',
            'funnel_stage' => 'Embudo',
            'client_progress' => 'Fase de ventas',
            'client_status' => 'Estado del cliente',
            'pay_status' => 'Estado de pago',
            'after_sales' => 'Posventa',
            'problem_detected' => 'Problema detectado',
            'demo_offered' => 'Demo ofrecida',
            'first_contact' => 'Primer contacto',
            'differentiator_explained' => 'Diferenciador explicado',
            'diagnosis_made' => 'Diagnóstico realizado',
            'probability' => 'Probabilidad',
        ];
    }

    public function displayValue(Lead $lead, string $field): string
    {
        $value = $lead->{$field};

        return match ($field) {
            'state_id' => $lead->state?->name ?? '—',
            'last_contact', 'creat_at' => $value?->format('d/m/Y H:i') ?? '—',
            'demo_offered', 'first_contact', 'differentiator_explained', 'diagnosis_made' => $value ? 'Sí' : 'No',
            'probability' => $value !== null ? $value.'%' : '—',
            'budget' => $value !== null ? '$'.number_format((float) $value, 2) : '—',
            default => filled($value) ? (string) $value : '—',
        };
    }

    public function confirmMerge(): void
    {
        $this->confirming = true;
    }

    public function cancelConfirm(): void
    {
        $this->confirming = false;
    }

    public function merge(): void
    {
        if (! in_array($this->survivorId, $this->leadIds, true)) {
            $this->error(title: 'No se pudo fusionar', description: 'Elige el lead que sobrevivirá.');

            return;
        }

        if (! in_array($this->billingLeadId, $this->leadIds, true)) {
            $this->error(title: 'No se pudo fusionar', description: 'Elige de qué lead se conserva la facturación.');

            return;
        }

        foreach ($this->fieldChoices as $field => $chosenLeadId) {
            if (! in_array($chosenLeadId, $this->leadIds, true)) {
                $this->error(title: 'No se pudo fusionar', description: "El valor elegido para '{$field}' no es válido.");

                return;
            }
        }

        if ($this->totalContactPeople > 0 && empty($this->contactPeopleIdsToKeep)) {
            $this->confirming = false;
            $this->error(title: 'No se pudo fusionar', description: 'Selecciona al menos un contacto para conservar.');

            return;
        }

        try {
            $survivor = app(LeadMergeService::class)->merge(
                leadIds: $this->leadIds,
                survivorId: $this->survivorId,
                fieldChoices: $this->fieldChoices,
                billingLeadId: $this->billingLeadId,
                contactPeopleIdsToKeep: $this->contactPeopleIdsToKeep,
            );
        } catch (\InvalidArgumentException $e) {
            $this->confirming = false;
            $this->error(title: 'No se pudo fusionar', description: $e->getMessage());

            return;
        }

        $this->success(
            title: '¡Leads fusionados!',
            description: 'Los leads seleccionados se combinaron correctamente.',
        );

        $this->redirect(route('leads.show', $survivor), navigate: true);
    }

    public function render()
    {
        return view('livewire.leads.merge');
    }
}
