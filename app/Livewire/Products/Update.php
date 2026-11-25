<?php

namespace App\Livewire\Products;

use App\Livewire\Forms\Products\ProductForm;
use App\Models\Companies\Company;
use App\Models\Products\Product;
use App\Models\Products\ProductCategory;
use App\Models\Products\ProductTag;
use App\Models\Products\ProductType;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Title('Editar Producto')]
class Update extends Component
{
    use Toast;
    use WithFileUploads;

    public ProductForm $productForm;

    public Product $product;

    public array $typeOptions = [];

    public array $categoryOptions = [];

    public array $tagOptions = [];

    public array $companyOptions = [];

    public string $tab = 'basic';

    public bool $typeModal = false;

    public bool $categoryModal = false;

    public bool $tagModal = false;

    public string $newTypeName = '';

    public string $newCategoryName = '';

    public string $newTagName = '';

    public function mount(Product $product): void
    {
        $this->product = $product->load(['type', 'category', 'tags', 'specifications']);

        $this->productForm->setProduct($product);

        // Obtener opciones
        $this->typeOptions = ProductType::all()->toArray();
        $this->categoryOptions = ProductCategory::all()->toArray();
        $this->tagOptions = ProductTag::all()->toArray();
        $this->companyOptions = Company::all()->map(fn ($company) => [
            'id' => $company->id,
            'name' => $company->name,
            'avatar' => $company->avatar_url,
        ])->toArray();
    }

    protected function rules(): array
    {
        return [];
    }

    public function update(): void
    {
        $this->validate();

        $this->productForm->update();

        $this->success(
            title: '¡Producto actualizado correctamente!',
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
        return view('livewire.products.update');
    }
}
