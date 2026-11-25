<?php

namespace App\Livewire\Quotes;

use App\Livewire\Forms\Quotes\QuoteForm;
use App\Models\Leads\Lead;
use App\Models\Products\Product;
use App\Models\Quotes\ExchangeRate;
use App\Services\ExchangeRateService;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Title('Crear Cotización')]
class Create extends Component
{
    use Toast, WithPagination;

    public Lead $lead;

    public QuoteForm $quoteForm;

    public string $prefillMode = '';

    public string $sourceFolio = '';

    public array $selectedCurrencies = [];

    public $productSearch = '';

    public array $productTagFilter = [];

    public array $productTagOptions = [];

    public const PRODUCTS_PER_PAGE_OPTIONS = [12, 24, 48];

    public int $productsPerPage = 12;

    protected $exchangeRateService;

    public bool $myModal2 = false;

    public string $noteContent = '';

    public bool $conceptModal = false;

    public array $conceptForm = [
        'name' => '',
        'description' => '',
        'unit_price' => 0,
        'quantity' => 1,
        'discount' => 0,
    ];

    public function boot(ExchangeRateService $exchangeRateService): void
    {
        $this->exchangeRateService = $exchangeRateService;
    }

    public function mount(Lead $lead): void
    {
        $this->lead = $lead;

        $this->exchangeRateService->ensureFreshRates();

        $this->quoteForm->company_id = $lead->company_id;
        $this->quoteForm->lead_id = $lead->id;

        $this->productTagOptions = \App\Models\Products\ProductTag::whereHas('products', fn ($q) => $q->where('company_id', $this->lead->company_id)->where('active', true)
        )->get()->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])->toArray();

        $prefill = session()->pull('quote_prefill');

        if ($prefill && isset($prefill['mode'], $prefill['source_version_id'])) {
            $sourceVersion = \App\Models\Quotes\QuoteVersion::with(['items.product', 'exchangeRates', 'notes', 'concepts'])
                ->find($prefill['source_version_id']);

            if ($sourceVersion) {
                $this->prefillMode = $prefill['mode'];
                $this->sourceFolio = $sourceVersion->folio;
                session()->forget('quote_items');

                if ($prefill['mode'] === 'revision') {
                    $this->quoteForm->prefillFromRevision($sourceVersion);
                } else {
                    $this->quoteForm->currents = ExchangeRate::allCurrent()->toArray();
                    $this->quoteForm->prefillFromRecotizacion($sourceVersion);
                }

                return;
            }
        }

        // Flujo estándar: nueva cotización desde cero
        $this->quoteForm->currents = ExchangeRate::allCurrent()->toArray();
        $this->quoteForm->items = session()->get('quote_items', []);
    }

    /**
     * Guardar la cotización - ¡EL MÉTODO QUE FALTA!
     */
    public function save()
    {
        // Validar
        $this->quoteForm->validate();

        try {
            // Guardar en transacción
            $quoteVersion = $this->quoteForm->save();

            // Mostrar mensaje de éxito
            $this->success(
                title: '¡Cotización creada!',
                description: "Folio: {$quoteVersion->folio}",
                timeout: 5000,
                redirectTo: route('leads.show', $this->lead)
            );

        } catch (\Exception $e) {
            // Manejar error
            $this->error(
                title: 'Error al guardar',
                description: $e->getMessage(),
                timeout: 5000,

            );

            // Loggear el error
            logger()->error('Error creando cotización', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    #[Computed]
    public function defaultNotes(): array
    {
        return \App\Models\Companies\CompanyDefaultNote::where('company_id', $this->quoteForm->company_id)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($n) => ['id' => $n->id, 'title' => $n->title, 'content' => $n->content])
            ->toArray();
    }

    #[Computed]
    public function filteredProducts(): LengthAwarePaginator
    {
        $companyId = $this->quoteForm->company_id ?: $this->lead->company_id;
        $search = trim($this->productSearch ?? '');

        return Product::query()
            ->where('company_id', $companyId)
            ->where('active', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when(! empty($this->productTagFilter), function ($query) {
                $query->whereHas('tags', fn ($q) => $q->whereIn('product_tags.id', $this->productTagFilter));
            })
            ->orderBy('name')
            ->paginate($this->productsPerPage, pageName: 'productsPage');
    }

    public function updatedProductSearch(): void
    {
        $this->resetPage('productsPage');
    }

    public function updatedProductTagFilter(): void
    {
        $this->resetPage('productsPage');
    }

    public function updatedProductsPerPage(): void
    {
        // Evita valores arbitrarios enviados desde el cliente
        if (! in_array($this->productsPerPage, self::PRODUCTS_PER_PAGE_OPTIONS, true)) {
            $this->productsPerPage = self::PRODUCTS_PER_PAGE_OPTIONS[0];
        }

        $this->resetPage('productsPage');
    }

    public function toggleProduct($productId)
    {
        $this->quoteForm->toggleProduct($productId);
    }

    public function updateQuantity($index, $quantity)
    {
        $this->quoteForm->updateQuantity($index, $quantity);
    }

    public function updateDiscount($index, $discount)
    {
        $this->quoteForm->updateDiscount($index, $discount);
    }

    public function updateCustomPrice($index, $price)
    {
        $this->quoteForm->updateCustomPrice($index, $price);
    }

    public function resetPrice($index)
    {
        $this->quoteForm->resetPrice($index);
    }

    public function removeItem($index)
    {
        $this->quoteForm->removeItem($index);
    }

    public function getSubtotalProperty()
    {
        return $this->quoteForm->getSubtotalProperty();
    }

    public function itemsTotalNoDiscount()
    {
        return $this->quoteForm->itemsTotalNoDiscount();
    }

    public function itemsTotalDiscount()
    {
        return $this->quoteForm->itemsTotalDiscount();
    }

    public function getTaxProperty()
    {
        return $this->quoteForm->getTaxProperty();
    }

    public function getTotalProperty()
    {
        return $this->quoteForm->getTotalProperty();
    }

    public function getCurrencyCalculationsProperty(): array
    {
        return $this->quoteForm->getCurrencyCalculationsProperty();
    }

    public function addNote($content)
    {
        $this->quoteForm->addNote($content);

        $this->noteContent = '';
        $this->myModal2 = false;
    }

    public function removeNote($index)
    {
        $this->quoteForm->removeNote($index);
    }

    public function addConcept(): void
    {
        $this->quoteForm->addConcept($this->conceptForm);
        $this->conceptForm = [
            'name' => '',
            'description' => '',
            'unit_price' => 0,
            'quantity' => 1,
            'discount' => 0,
        ];
        $this->conceptModal = false;
    }

    public function removeConcept(int $index): void
    {
        $this->quoteForm->removeConcept($index);
    }

    public function updateConcept(int $index, string $field, $value): void
    {
        $this->quoteForm->updateConcept($index, $field, $value);
    }

    public function updateExchangeRate($currency, $mount)
    {
        $this->quoteForm->updateExchangeRate($currency, $mount);
    }

    public function with(): array
    {
        return [
            'products' => $this->filteredProducts,
            'productTagOptions' => $this->productTagOptions,
        ];
    }

    public function render()
    {
        return view('livewire.quotes.create', [
            'itemsTotalNoDiscount' => $this->itemsTotalNoDiscount(),
            'itemsTotalDiscount' => $this->itemsTotalDiscount(),
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'total' => $this->total,
            'items' => $this->quoteForm->items,
            'concepts' => $this->quoteForm->concepts,
            'defaultNotes' => $this->defaultNotes,
        ]);
    }
}
