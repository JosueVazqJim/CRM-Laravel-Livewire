<?php

use App\Livewire\Quotes\Create;
use App\Models\Companies\Company;
use App\Models\Leads\Lead;
use App\Models\Products\Product;
use App\Models\Products\ProductTag;
use App\Models\Quotes\ExchangeRate;
use App\Models\Users\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    // Tasas del día para que ensureFreshRates() no llame a Banxico
    foreach (['USD' => 1, 'MXN' => 18.5] as $currency => $rate) {
        ExchangeRate::create([
            'base_currency' => 'USD',
            'target_currency' => $currency,
            'rate' => $rate,
            'valid_at' => now(),
        ]);
    }

    $this->company = Company::factory()->create();
    $this->admin = User::factory()->create();
    $this->admin->syncRoles(['admin']);
    $this->lead = Lead::factory()->for($this->company)->create();
});

function makeProduct(Company $company, string $name, array $attributes = []): Product
{
    return Product::create(array_merge([
        'company_id' => $company->id,
        'name' => $name,
        'sku' => 'SKU-'.str()->random(8),
        'currency' => 'USD',
        'price_regular' => 100,
        'active' => true,
    ], $attributes));
}

test('the catalog is paginated at 12 products per page', function () {
    foreach (range(1, 15) as $i) {
        makeProduct($this->company, sprintf('Producto %02d', $i));
    }

    $component = Livewire::actingAs($this->admin)->test(Create::class, ['lead' => $this->lead]);

    $page = $component->instance()->filteredProducts();
    expect($page->total())->toBe(15)
        ->and($page->count())->toBe(12);

    $component->call('nextPage', 'productsPage');
    unset($component->instance()->filteredProducts);

    expect($component->instance()->filteredProducts()->count())->toBe(3);
});

test('only active products of the lead company are listed', function () {
    makeProduct($this->company, 'Activo');
    makeProduct($this->company, 'Inactivo', ['active' => false]);
    makeProduct(Company::factory()->create(), 'Otra empresa');

    $names = Livewire::actingAs($this->admin)
        ->test(Create::class, ['lead' => $this->lead])
        ->instance()->filteredProducts()->pluck('name')->all();

    expect($names)->toBe(['Activo']);
});

test('search filters by name or sku and resets to the first page', function () {
    foreach (range(1, 15) as $i) {
        makeProduct($this->company, sprintf('Producto %02d', $i));
    }
    makeProduct($this->company, 'Cámara térmica', ['sku' => 'CAM-001']);
    makeProduct($this->company, 'Sensor', ['sku' => 'CAM-002']);

    $component = Livewire::actingAs($this->admin)
        ->test(Create::class, ['lead' => $this->lead])
        ->call('nextPage', 'productsPage')
        ->set('productSearch', 'cam');

    expect($component->get('paginators')['productsPage'])->toBe(1)
        ->and($component->instance()->filteredProducts()->pluck('name')->all())
        ->toBe(['Cámara térmica', 'Sensor']);
});

test('tag filter returns only products with the selected tags', function () {
    $tag = ProductTag::create(['name' => 'Exterior']);

    makeProduct($this->company, 'Con etiqueta')->tags()->attach($tag);
    makeProduct($this->company, 'Sin etiqueta');

    $names = Livewire::actingAs($this->admin)
        ->test(Create::class, ['lead' => $this->lead])
        ->set('productTagFilter', [$tag->id])
        ->instance()->filteredProducts()->pluck('name')->all();

    expect($names)->toBe(['Con etiqueta']);
});

test('per page selector changes page size and resets to the first page', function () {
    foreach (range(1, 30) as $i) {
        makeProduct($this->company, sprintf('Producto %02d', $i));
    }

    $component = Livewire::actingAs($this->admin)
        ->test(Create::class, ['lead' => $this->lead])
        ->call('nextPage', 'productsPage')
        ->set('productsPerPage', 24);

    expect($component->get('paginators')['productsPage'])->toBe(1)
        ->and($component->instance()->filteredProducts()->count())->toBe(24);
});

test('per page selector ignores values outside the allowed options', function () {
    $component = Livewire::actingAs($this->admin)
        ->test(Create::class, ['lead' => $this->lead])
        ->set('productsPerPage', 5000);

    expect($component->get('productsPerPage'))->toBe(12);
});
