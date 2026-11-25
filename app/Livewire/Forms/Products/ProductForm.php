<?php

namespace App\Livewire\Forms\Products;

use AllowDynamicProperties;
use App\Models\Products\Product;
use Livewire\Form;

#[AllowDynamicProperties]
class ProductForm extends Form
{
    public ?Product $productModel = null;

    public string $name = '';

    public string $sku = '';

    public int $company_id = 0;

    public int $product_type_id = 0;

    public int $product_category_id = 0;

    public string $currency = 'USD';

    public float $price_regular = 0;

    public float $price_min = 0;

    public float $price_max = 0;

    public float $installation_cost = 0;

    public string $color = '#FF0000';

    public string $short_description = '';

    public string $long_description = '';

    public bool $active = true;

    public $image_path = null;

    public array $tags = [];

    public array $specifications = [
        ['name' => '', 'value' => '', 'sort_order' => 0],
    ];

    public function rules(): array
    {
        $productId = $this->productModel?->id ?? null;

        $isImageUpload = $this->image_path && is_object($this->image_path) && method_exists($this->image_path, 'store');

        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'.($productId ? ",$productId" : '')],
            'company_id' => ['required', 'exists:companies,id'],
            'product_type_id' => ['required', 'exists:product_types,id'],
            'product_category_id' => ['required', 'exists:product_categories,id'],
            'currency' => ['nullable', 'string', 'max:3'],
            'price_regular' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'price_min' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'price_max' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'installation_cost' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'color' => ['nullable', 'string', 'max:50'],
            'short_description' => ['nullable', 'string'],
            'long_description' => ['nullable', 'string'],
            'active' => ['boolean'],
            'image_path' => $isImageUpload ? ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:10240'] : ['nullable'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:product_tags,id'],
            'specifications' => ['nullable', 'array'],
            'specifications.*.name' => ['nullable', 'string', 'max:255'],
            'specifications.*.value' => ['nullable', 'string'],
            'specifications.*.sort_order' => ['nullable', 'integer'],
        ];
    }

    public function setProduct(Product $product): void
    {
        $this->productModel = $product;

        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->company_id = $product->company_id;
        $this->product_type_id = $product->product_type_id ?? 0;
        $this->product_category_id = $product->product_category_id ?? 0;
        $this->currency = $product->currency ?? 'USD';
        $this->price_regular = (float) $product->price_regular;
        $this->price_min = (float) $product->price_min;
        $this->price_max = (float) $product->price_max;
        $this->installation_cost = (float) $product->installation_cost;
        $this->color = $product->color ?? '';
        $this->short_description = $product->short_description ?? '';
        $this->long_description = $product->long_description ?? '';
        $this->active = (bool) $product->active;
        $this->image_path = $product->image_path;

        // Cargar tags
        $this->tags = $product->tags->pluck('id')->toArray();

        // Cargar specifications
        if ($product->specifications->isNotEmpty()) {
            $this->specifications = $product->specifications->map(fn ($spec) => [
                'name' => $spec->spec_key,
                'value' => $spec->spec_value,
                'sort_order' => $spec->sort_order,
            ])->toArray();
        }
    }

    public function store(): Product
    {
        //        $imagePath = $this->image_path
        //            ? $this->image_path->store('products', 'public')
        //            : 'images/no-image.jpg';
        $imagePath = $this->image_path
            ? $this->image_path->store("products/{$this->company_id}", 'public')
            : 'images/no-image.jpg';

        $product = Product::create([
            'name' => $this->name,
            'sku' => $this->sku,
            'company_id' => $this->company_id,
            'product_type_id' => $this->product_type_id,
            'product_category_id' => $this->product_category_id,
            'currency' => $this->currency,
            'price_regular' => $this->price_regular,
            'price_min' => $this->price_min ?? 0,
            'price_max' => $this->price_max ?? 0,
            'installation_cost' => $this->installation_cost ?? 0,
            'color' => $this->color,
            'short_description' => $this->short_description,
            'long_description' => $this->long_description,
            'image_path' => $imagePath,
            'active' => $this->active,
        ]);

        $this->syncTags($product);
        $this->syncSpecifications($product);

        return $product;
    }

    public function update(): Product
    {
        //        if ($this->image_path && is_object($this->image_path) && method_exists($this->image_path, 'store')) {
        //            $imagePath = $this->image_path->store('products', 'public');
        //        } elseif (is_string($this->image_path) && ! empty($this->image_path)) {
        //            $imagePath = $this->image_path;
        //        } else {
        //            $imagePath = 'images/no-image.jpg';
        //        }

        if ($this->image_path && is_object($this->image_path) && method_exists($this->image_path, 'store')) {
            if ($this->productModel->image_path && $this->productModel->image_path !== 'images/no-image.jpg') {
                \Storage::disk('public')->delete($this->productModel->image_path);
            }
            $imagePath = $this->image_path->store("products/{$this->company_id}", 'public');
        } elseif (is_string($this->image_path) && ! empty($this->image_path)) {
            $imagePath = $this->image_path;
        } else {
            $imagePath = 'images/no-image.jpg';
        }

        $data = [
            'name' => $this->name,
            'sku' => $this->sku,
            'company_id' => $this->company_id,
            'product_type_id' => $this->product_type_id,
            'product_category_id' => $this->product_category_id,
            'currency' => $this->currency,
            'price_regular' => $this->price_regular,
            'price_min' => $this->price_min ?? 0,
            'price_max' => $this->price_max ?? 0,
            'installation_cost' => $this->installation_cost ?? 0,
            'color' => $this->color,
            'short_description' => $this->short_description,
            'long_description' => $this->long_description,
            'image_path' => $imagePath,
            'active' => $this->active,
        ];

        $this->productModel->update($data);

        $this->syncTags($this->productModel);
        $this->syncSpecifications($this->productModel);

        return $this->productModel->fresh();
    }

    protected function syncTags(Product $product): void
    {
        if (! empty($this->tags)) {
            $product->tags()->sync($this->tags);
        } else {
            $product->tags()->detach();
        }
    }

    protected function syncSpecifications(Product $product, bool $deleteExisting = true): void
    {
        if ($deleteExisting) {
            $product->specifications()->delete();
        }

        if (! empty($this->specifications)) {
            // verificar que cada especificación tenga un nombre válido antes de crearla
            $validSpecifications = array_filter($this->specifications, fn ($spec) => ! empty($spec['name'])
            );

            if (! empty($validSpecifications)) {
                foreach ($validSpecifications as $spec) {
                    $product->specifications()->create([
                        'spec_key' => $spec['name'],
                        'spec_value' => $spec['value'] ?? '',
                        'sort_order' => $spec['sort_order'] ?? 0,
                    ]);
                }
            }
        }
    }

    public function addSpecification(): void
    {
        $this->specifications[] = [
            'name' => '',
            'value' => '',
            'sort_order' => count($this->specifications),
        ];
    }

    public function removeSpecification(int $index): void
    {
        unset($this->specifications[$index]);

        $this->specifications = array_values($this->specifications);
    }
}
