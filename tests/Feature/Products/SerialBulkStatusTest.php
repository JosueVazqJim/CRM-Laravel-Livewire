<?php

use App\Livewire\Products\Serials\Index;
use App\Livewire\Serials\StatusTransition;
use App\Models\Companies\Company;
use App\Models\Products\Product;
use App\Models\Products\ProductSerial;
use App\Models\Products\StockMovement;
use App\Models\Users\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->syncRoles(['admin']);

    $this->product = Product::create([
        'company_id' => Company::factory()->create()->id,
        'name' => 'Terminal de prueba',
        'sku' => 'SKU-'.str()->random(8),
        'slug' => 'terminal-de-prueba',
        'currency' => 'USD',
        'active' => true,
    ]);

    $this->serials = collect(range(1, 3))->map(fn (int $i) => ProductSerial::create([
        'product_id' => $this->product->id,
        'serial_number' => "SN-000{$i}",
        'status' => ProductSerial::STATUS_IN_STOCK,
    ]));
});

test('bulk delete removes the selected serials, their movements and updates stock', function () {
    [$first, $second, $third] = $this->serials;
    $first->transition(ProductSerial::STATUS_DAMAGED);
    $first->transition(ProductSerial::STATUS_IN_STOCK);

    Livewire::actingAs($this->admin)
        ->test(Index::class, ['product' => $this->product])
        ->set('selectedSerials', [$first->id, $second->id])
        ->call('openBulkStatusModal')
        ->set('bulk_status', Index::BULK_DELETE)
        ->call('confirmBulkStatus')
        ->assertHasNoErrors()
        ->assertSet('showBulkStatusModal', false)
        ->assertSet('selectedSerials', []);

    expect(ProductSerial::pluck('id')->all())->toBe([$third->id])
        ->and(StockMovement::where('product_serial_id', $first->id)->exists())->toBeFalse()
        ->and($this->product->fresh()->stock_count)->toBe(1);
});

test('bulk delete is rejected without the seriales.eliminar permission', function () {
    $user = User::factory()->create();
    $user->syncRoles([]); // la factory asigna "vendedor", que sí puede eliminar

    Livewire::actingAs($user)
        ->test(Index::class, ['product' => $this->product])
        ->set('selectedSerials', [$this->serials[0]->id])
        ->set('bulk_status', Index::BULK_DELETE)
        ->call('confirmBulkStatus')
        ->assertHasErrors(['bulk_status']);

    expect(ProductSerial::count())->toBe(3);
});

test('bulk status change transitions the selected serials and records movements', function () {
    [$first, $second] = $this->serials;

    Livewire::actingAs($this->admin)
        ->test(Index::class, ['product' => $this->product])
        ->set('selectedSerials', [$first->id, $second->id])
        ->set('bulk_status', ProductSerial::STATUS_DEFECTIVE)
        ->call('confirmBulkStatus')
        ->assertHasNoErrors();

    expect($first->fresh()->status)->toBe(ProductSerial::STATUS_DEFECTIVE)
        ->and($second->fresh()->status)->toBe(ProductSerial::STATUS_DEFECTIVE)
        ->and(StockMovement::where('to_status', ProductSerial::STATUS_DEFECTIVE)->count())->toBe(2)
        ->and($this->product->fresh()->stock_count)->toBe(1);
});

test('bulk sold still requires the seller', function () {
    Livewire::actingAs($this->admin)
        ->test(Index::class, ['product' => $this->product])
        ->set('selectedSerials', [$this->serials[0]->id])
        ->call('openBulkStatusModal')
        ->set('bulk_status', ProductSerial::STATUS_SOLD)
        ->call('confirmBulkStatus')
        ->assertHasErrors(['bulk_sold_by_user_id']);

    expect($this->serials[0]->fresh()->status)->toBe(ProductSerial::STATUS_IN_STOCK);
});

test('a serial can be deleted from the status card on its show page', function () {
    $serial = $this->serials[0];

    Livewire::actingAs($this->admin)
        ->test(StatusTransition::class, ['serial' => $serial])
        ->assertSee('Eliminar Serial')
        ->call('deleteSerial')
        ->assertRedirect(route('products.serials.index', $this->product));

    expect(ProductSerial::find($serial->id))->toBeNull()
        ->and($this->product->fresh()->stock_count)->toBe(2);
});

test('the status card hides and blocks delete without the seriales.eliminar permission', function () {
    $serial = $this->serials[0];

    $user = User::factory()->create();
    $user->syncRoles([]); // la factory asigna "vendedor", que sí puede eliminar

    Livewire::actingAs($user)
        ->test(StatusTransition::class, ['serial' => $serial])
        ->assertDontSee('Eliminar Serial')
        ->call('deleteSerial')
        ->assertNoRedirect();

    expect(ProductSerial::find($serial->id))->not->toBeNull();
});
