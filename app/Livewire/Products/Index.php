<?php

namespace App\Livewire\Products;

use App\Livewire\Traits\ClearsProperties;
use App\Models\Companies\Company;
use App\Models\Products\Product;
use App\Models\Products\ProductCategory;
use App\Models\Products\ProductTag;
use App\Models\Products\ProductType;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Productos')]

class Index extends Component
{
    use ClearsProperties, WithPagination;

    public string $search = '';

    public string $sku = '';

    public string $category_id = '';

    public array $categoriesOptions = [];

    public string $type_id = '';

    public array $typesOptions = [];

    public int $company = 0;

    public array $companyOptions = [];

    public array $tags = [];

    public array $tagsOptions = [];

    public string $currency = '';

    public array $currencyOptions = [];

    public string $created_at = '';

    public string $updated_at = '';

    public array $sortBy = ['column' => 'updated_at', 'direction' => 'desc'];

    public bool $showFilters = false;

    // Paginación
    public int $perPage = 15;

    public const PER_PAGE_OPTIONS = [15, 30, 50, 100];

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            $companies = Company::where('active', true)->get();
        } else {
            $companies = $user->companies()->where('active', true)->get();
        }

        $this->companyOptions = $companies->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'avatar' => $c->avatar_url,
        ])->toArray();

        if (! empty($this->companyOptions)) {
            $this->company = $this->companyOptions[0]['id'];
        }

        $this->categoriesOptions = ProductCategory::all()->toArray();
        $this->typesOptions = ProductType::all()->toArray();
        $this->tagsOptions = ProductTag::query()->orderBy('name')->get(['id', 'name'])->toArray();

        $this->currencyOptions = Product::whereNotNull('currency')
            ->where('currency', '!=', '')
            ->select('currency')
            ->distinct()
            ->pluck('currency')
            ->map(fn ($currency) => [
                'id' => $currency,
                'name' => $currency,
            ])
            ->values()
            ->toArray();
    }

    // IMPORTANTE: Resetear paginación cuando cambian los filtros
    public function updated($property): void
    {
        if (in_array($property, ['search', 'sku', 'category_id', 'type_id', 'tags', 'currency', 'company', 'created_at', 'updated_at', 'perPage'])) {
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
        if (! empty($this->sku)) {
            $count++;
        }
        if (! empty($this->category_id)) {
            $count++;
        }
        if (! empty($this->type_id)) {
            $count++;
        }
        if (! empty($this->tags)) {
            $count++;
        }
        if (! empty($this->currency)) {
            $count++;
        }
        if (! empty($this->created_at)) {
            $count++;
        }
        if (! empty($this->updated_at)) {
            $count++;
        }

        return $count;
    }

    // Método principal que obtiene los productos con filtros
    public function getProductsProperty(): LengthAwarePaginator
    {
        $query = Product::query();

        // Siempre filtrar por company seleccionada
        if ($this->company) {
            $query->where('company_id', $this->company);
        }

        $query->where('active', true);

        // Vendedor solo ve productos de sus companies asignadas
        if (! auth()->user()->hasRole('admin')) {
            $userCompanyIds = auth()->user()->companies()
                ->pluck('companies.id')->toArray();
            $query->whereIn('company_id', $userCompanyIds);
        }

        // BÚSQUEDA GLOBAL - Busca en múltiples campos
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('sku', 'like', '%'.$this->search.'%')
                    ->orWhere('short_description', 'like', '%'.$this->search.'%');

                // Si el search parece un número, buscar por ID
                if (is_numeric($this->search)) {
                    $q->orWhere('id', $this->search);
                }
            });
        }

        // Aplicar filtros SOLO si tienen valor
        if (! empty($this->sku)) {
            $query->where('sku', 'like', '%'.$this->sku.'%');
        }

        if (! empty($this->category_id)) {
            $query->where('product_category_id', $this->category_id);
        }

        if (! empty($this->type_id)) {
            $query->where('product_type_id', $this->type_id);
        }

        // Productos que tengan al menos una de las etiquetas seleccionadas
        if (! empty($this->tags)) {
            $query->whereHas('tags', fn ($q) => $q->whereIn('product_tags.id', $this->tags));
        }

        if (! empty($this->currency)) {
            $query->where('currency', $this->currency);
        }

        if (! empty($this->created_at)) {
            $query->whereDate('created_at', 'like', '%'.$this->created_at.'%');
        }

        if (! empty($this->updated_at)) {
            $query->whereDate('updated_at', 'like', '%'.$this->updated_at.'%');
        }

        // Aplicar ordenamiento
        return $query
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function headers(): array
    {
        return [
            ['key' => 'image_path', 'label' => '', 'class' => 'w-14', 'sortable' => false],
            ['key' => 'sku', 'label' => 'SKU', 'sortBy' => 'sku'],
            ['key' => 'name', 'label' => 'Nombre', 'sortBy' => 'name'],
            ['key' => 'category.name', 'label' => 'Categoría', 'sortable' => false],
            ['key' => 'type.name', 'label' => 'Tipo', 'sortable' => false],
            ['key' => 'price_regular', 'label' => 'Precio', 'sortBy' => 'price_regular'],
            ['key' => 'currency', 'label' => 'Moneda', 'sortBy' => 'currency'],
            ['key' => 'stock_count', 'label' => 'Stock', 'sortBy' => 'stock_count', 'class' => 'text-center'],
            ['key' => 'active', 'label' => 'Estado', 'class' => 'text-center'],
        ];
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'products' => $this->products,
            'filterCount' => $this->filterCount,
            'companyOptions' => $this->companyOptions,
        ];
    }

    protected function filters(): array
    {
        return ['category_id', 'type_id', 'tags', 'currency', 'sku', 'created_at', 'updated_at'];
    }

    public function render()
    {
        return view('livewire.products.index');
    }
}
