<?php

namespace App\Livewire\Products;

use App\Livewire\Forms\Products\ProductForm;
use App\Models\Companies\Company;
use App\Models\Products\ProductCategory;
use App\Models\Products\ProductTag;
use App\Models\Products\ProductType;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Title('Crear Producto')]
class Create extends Component
{
    use Toast;
    use WithFileUploads;

    public ProductForm $productForm;

    public array $typeOptions = [];

    public array $categoryOptions = [];

    public array $companyOptions = [];

    public array $tagOptions = [];

    public string $tab = 'basic';

    public bool $typeModal = false;

    public bool $categoryModal = false;

    public bool $tagModal = false;

    public string $newTypeName = '';

    public string $newCategoryName = '';

    public string $newTagName = '';

    public function mount(): void
    {
        $this->productForm = new ProductForm($this, 'productForm');

        // Obtener opciones
        $this->typeOptions = ProductType::all()->toArray();
        $this->categoryOptions = ProductCategory::all()->toArray();
        $this->tagOptions = ProductTag::all()->toArray();

        $this->companyOptions = Company::all()->map(fn ($company) => [
            'id' => $company->id,
            'name' => $company->name,
            'avatar' => $company->avatar_url,
        ])->toArray();

        $this->productForm->currency = 'USD';
        $this->productForm->active = true;
    }

    protected function rules(): array
    {
        return [];
    }

    public function save(): void
    {
        $this->validate();

        $this->productForm->store();

        $this->success(
            title: '¡Producto creado correctamente!',
            redirectTo: route('products.index')
        );
    }

    public function getTabErrors(): array
    {
        $errors = collect($this->getErrorBag()->toArray())->keys();

        return [
            'basic' => $errors->filter(fn ($k) => in_array($k, ['productForm.name', 'productForm.sku', 'productForm.company_id', 'productForm.product_type_id', 'productForm.product_category_id', 'productForm.currency']))->count(),
            'pricing' => $errors->filter(fn ($k) => str_starts_with($k, 'productForm.price') || str_starts_with($k, 'productForm.installation'))->count(),
            'description' => $errors->filter(fn ($k) => in_array($k, ['productForm.short_description', 'productForm.long_description', 'productForm.color', 'productForm.image_path']))->count(),
            'advanced' => $errors->filter(fn ($k) => in_array($k, ['productForm.tags', 'productForm.specifications']))->count(),
        ];
    }

    public function saveNewType(): void
    {
        $this->validate(['newTypeName' => 'required|string|max:255|unique:product_types,name']);
        $type = ProductType::create(['name' => $this->newTypeName]);
        $this->typeOptions[] = $type->toArray();
        $this->productForm->product_type_id = $type->id;
        $this->newTypeName = '';
        $this->typeModal = false;
        $this->success('Tipo creado correctamente.');
    }

    public function saveNewCategory(): void
    {
        $this->validate(['newCategoryName' => 'required|string|max:255|unique:product_categories,name']);
        $category = ProductCategory::create(['name' => $this->newCategoryName]);
        $this->categoryOptions[] = $category->toArray();
        $this->productForm->product_category_id = $category->id;
        $this->newCategoryName = '';
        $this->categoryModal = false;
        $this->success('Categoría creada correctamente.');
    }

    public function saveNewTag(): void
    {
        $this->validate(['newTagName' => 'required|string|max:255|unique:product_tags,name']);
        $tag = ProductTag::create(['name' => $this->newTagName]);
        $this->tagOptions[] = $tag->toArray();
        $currentTags = $this->productForm->tags ?? [];
        $this->productForm->tags = array_merge($currentTags, [$tag->id]);
        $this->newTagName = '';
        $this->tagModal = false;
        $this->success('Etiqueta creada correctamente.');
    }

    public function addSpecification(): void
    {
        $this->productForm->addSpecification();
    }

    public function removeSpecification(int $index): void
    {
        $this->productForm->removeSpecification($index);
    }

    public function render()
    {
        return view('livewire.products.create');
    }
}
