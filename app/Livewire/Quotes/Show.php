<?php

namespace App\Livewire\Quotes;

use App\Livewire\Forms\Quotes\QuoteForm;
use App\Models\Quotes\QuoteVersion;
use App\Services\Quotes\CurrencySummaryService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Detalles de Cotización')]
class Show extends Component
{
    use Toast;

    public QuoteVersion $quoteVersion;

    public QuoteForm $quoteForm;

    // Control de acceso y UI
    public bool $showFullDetails = true;

    public string $activeTab = 'details'; // details, exchange_rates, history

    public string $tab = 'details';

    // Preview modal
    public bool $previewOpen = false;

    // Estado seleccionado para cambio
    public string $selectedStatus = '';

    // Modal de Orden de Compra
    public bool $purchaseOrderModalOpen = false;

    public ?float $poAdvanceAmount = null;

    public ?string $poAdvancePaidAt = null;

    public ?string $poPaymentMethod = null;

    public ?string $poReference = null;

    public ?string $poSettlementDate = null;

    #[Computed]
    public function previewUrl(): string
    {
        return route('quotes.preview', [
            'quoteVersion' => $this->quoteVersion->id,
            // Cache-buster: fuerza al iframe a recargar cuando cambian datos relacionados
            // (p. ej. la orden de compra), ya que el src en sí no cambiaría de otro modo.
            'v' => $this->quoteVersion->purchaseOrder?->updated_at?->timestamp ?? $this->quoteVersion->updated_at->timestamp,
        ]);
    }

    #[Computed]
    public function pdfUrl(): string
    {
        return route('quotes.pdf.download', ['quoteVersion' => $this->quoteVersion->id]);
    }

    #[Computed]
    public function hasMxnRate(): bool
    {
        return $this->quoteVersion->exchangeRates
            ->contains(fn ($rate) => $rate->from_currency === 'USD' && $rate->to_currency === 'MXN');
    }

    public function mount(QuoteVersion $quoteVersion): void
    {
        $this->quoteVersion = $quoteVersion->load([
            'quote.user',
            'quote.company',
            'quote.lead.contactPeople.channels',
            'quote.lead.billingInfo.address',
            'items.product',
            'exchangeRates',
            'notes',
            'concepts',
            'parentVersionQuote',
            'purchaseOrder',
        ]);

        // Cargar datos en el form para mostrar
        $this->quoteForm->loadQuote($this->quoteVersion);

        $this->selectedStatus = $this->quoteVersion->status;
    }

    /**
     * Cambiar estado de la cotización
     */
    public function updatedSelectedStatus(string $value): void
    {
        $allowedTransitions = [
            'en_proceso' => ['en_validacion', 'exitosa', 'sin_exito', 'cancelada', 'papelera'],
            'en_validacion' => ['en_proceso', 'exitosa', 'sin_exito', 'cancelada', 'papelera'],
            'exitosa' => ['en_proceso', 'en_validacion', 'sin_exito', 'cancelada', 'papelera'],
            'sin_exito' => ['en_proceso', 'exitosa', 'en_validacion', 'cancelada', 'papelera'],
            'cancelada' => ['en_proceso', 'exitosa', 'sin_exito', 'en_validacion', 'papelera'],
            'papelera' => ['en_proceso', 'exitosa', 'sin_exito', 'cancelada', 'en_validacion'],
        ];

        if (! in_array($value, $allowedTransitions[$this->quoteVersion->status] ?? [])) {
            $this->error('Transición de estado no permitida.');
            $this->selectedStatus = $this->quoteVersion->status;

            return;
        }

        $this->quoteVersion->update(['status' => $value]);
        $this->quoteVersion->refresh();
        $this->success('Estado actualizado correctamente');

        if ($value === 'exitosa') {
            $this->openPurchaseOrderModal();
        }
    }

    /**
     * Abre el modal de Orden de Compra, precargando los datos existentes (si los hay).
     */
    public function openPurchaseOrderModal(): void
    {
        $purchaseOrder = $this->quoteVersion->purchaseOrder;

        $this->poAdvanceAmount = $purchaseOrder?->advance_amount !== null ? (float) $purchaseOrder->advance_amount : null;
        $this->poAdvancePaidAt = $purchaseOrder?->advance_paid_at?->format('Y-m-d');
        $this->poPaymentMethod = $purchaseOrder?->payment_method;
        $this->poReference = $purchaseOrder?->reference;
        $this->poSettlementDate = $purchaseOrder?->settlement_date?->format('Y-m-d');

        $this->purchaseOrderModalOpen = true;
    }

    /**
     * Guarda (crea o actualiza) los datos de Orden de Compra de la versión actual.
     */
    public function savePurchaseOrder(): void
    {
        $validated = $this->validate([
            'poAdvanceAmount' => 'nullable|numeric|min:0',
            'poAdvancePaidAt' => 'nullable|date',
            'poPaymentMethod' => 'nullable|string|max:255',
            'poReference' => 'nullable|string|max:255',
            'poSettlementDate' => 'nullable|date',
        ]);

        $this->quoteVersion->purchaseOrder()->updateOrCreate(
            ['quote_version_id' => $this->quoteVersion->id],
            [
                'advance_amount' => $validated['poAdvanceAmount'],
                'advance_paid_at' => $validated['poAdvancePaidAt'],
                'payment_method' => $validated['poPaymentMethod'],
                'reference' => $validated['poReference'],
                'settlement_date' => $validated['poSettlementDate'],
            ]
        );

        $this->quoteVersion->load('purchaseOrder');
        $this->purchaseOrderModalOpen = false;
        $this->success('Orden de compra guardada correctamente');
    }

    /**
     * Total en MXN de la versión actual (usa el snapshot de tasas de cambio).
     */
    public function purchaseOrderMxnTotal(): ?float
    {
        $summary = collect(CurrencySummaryService::calculate($this->quoteVersion))
            ->firstWhere('currency', 'MXN');

        return $summary['total'] ?? null;
    }

    /**
     * Obtener badge de estado
     */
    public function getStatusBadge(): array
    {
        return match ($this->quoteVersion->status) {
            'en_proceso' => ['label' => 'En Proceso',    'class' => 'badge-info',    'icon' => 'o-clock'],
            'en_validacion' => ['label' => 'En Validación', 'class' => 'badge-warning', 'icon' => 'o-check-badge'],
            'exitosa' => ['label' => 'Exitosa',       'class' => 'badge-success', 'icon' => 'o-check-circle'],
            'sin_exito' => ['label' => 'Sin Éxito',     'class' => 'badge-error',   'icon' => 'o-x-circle'],
            'cancelada' => ['label' => 'Cancelada',     'class' => 'badge-neutral', 'icon' => 'o-ban'],
            'papelera' => ['label' => 'Papelera',      'class' => 'badge-ghost',   'icon' => 'o-trash'],
            default => ['label' => $this->quoteVersion->status, 'class' => 'badge-ghost', 'icon' => 'o-document'],
        };
    }

    /**
     * Formatear moneda
     */
    public function formatCurrency(float $amount, string $currency = 'USD'): string
    {
        $symbols = [
            'USD' => '$',
            'MXN' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ];

        $symbol = $symbols[$currency] ?? '$';

        return $symbol.' '.number_format($amount, 2);
    }

    /**
     * Inicia una REVISIÓN: copia la versión actual con snapshot histórico de tasas.
     * Redirige al formulario Create pre-llenado.
     */
    public function startRevision(): void
    {
        session()->put('quote_prefill', [
            'mode' => 'revision',
            'source_version_id' => $this->quoteVersion->id,
        ]);

        redirect()->route('quotes.create', $this->quoteVersion->quote->lead_id);
    }

    /**
     * Inicia una RECOTIZACIÓN: nueva cotización independiente con tasas frescas.
     * Redirige al formulario Create pre-llenado.
     */
    public function startRecotizacion(): void
    {
        session()->put('quote_prefill', [
            'mode' => 'recotizacion',
            'source_version_id' => $this->quoteVersion->id,
        ]);

        redirect()->route('quotes.create', $this->quoteVersion->quote->lead_id);
    }

    // public function print()
    // {
    //     $this->dispatch('print-quote');
    // }

    // public function sendByEmail()
    // {
    //     $this->success('Cotización enviada por email');
    // }

    // public function exportPDF()
    // {
    //     return redirect()->route('quotes.pdf', $this->quoteVersion);
    // }

    public function headers(): array
    {
        return [
            ['key' => 'product', 'label' => 'Producto'],
            ['key' => 'sku', 'label' => 'SKU'],
            ['key' => 'quantity', 'label' => 'Cantidad'],
            ['key' => 'unit_price_original', 'label' => 'Precio Original'],
            ['key' => 'exchange_rate_used', 'label' => 'Tasa Aplicada'],
            ['key' => 'unit_price_converted', 'label' => 'Precio Convertido'],
            ['key' => 'unit_price_converted_custom', 'label' => 'Precio Convertido Editado'],
            ['key' => 'discount', 'label' => 'Descuento'],
            ['key' => 'subtotal', 'label' => 'Subtotal'],
            ['key' => 'total', 'label' => 'Total'],
        ];
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'statusOptions' => [
                ['id' => 'en_proceso',    'name' => 'En Proceso'],
                ['id' => 'en_validacion', 'name' => 'En Validación'],
                ['id' => 'exitosa',       'name' => 'Exitosa'],
                ['id' => 'sin_exito',     'name' => 'Sin Éxito'],
                ['id' => 'cancelada',     'name' => 'Cancelada'],
                ['id' => 'papelera',      'name' => 'Papelera'],
            ],
        ];
    }

    public function render()
    {
        return view('livewire.quotes.show', [
            'statusBadge' => $this->getStatusBadge(),
            'previousVersion' => $this->quoteForm->getPreviousVersionInfo(),
            'isRecotization' => $this->quoteForm->isRecotization(),
            'currencySummary' => $this->quoteForm->getCurrencySummary(),
            'parentVersion' => $this->quoteVersion->parentVersionQuote,
            'childVersions' => $this->quoteVersion->childVersionQuotes()->orderBy('version_letter')->get(),
            'concepts' => $this->quoteForm->concepts,
            'purchaseOrder' => $this->quoteVersion->purchaseOrder,
            'poTotalMxn' => $this->purchaseOrderMxnTotal(),
        ]);
    }
}
