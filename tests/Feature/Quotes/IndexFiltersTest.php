<?php

use App\Livewire\Quotes\Index;
use App\Models\Companies\Company;
use App\Models\Leads\Lead;
use App\Models\Quotes\Quote;
use App\Models\Quotes\QuoteVersion;
use App\Models\Users\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->company = Company::factory()->create();
    $this->otherCompany = Company::factory()->create();

    $this->admin = User::factory()->create();
    $this->admin->syncRoles(['admin']);

    $this->seller = User::factory()->create();
    $this->seller->syncRoles(['vendedor']);
    $this->seller->companies()->attach($this->company->id);
});

function makeQuoteVersion(User $user, Company $company, array $attributes = []): QuoteVersion
{
    $quote = Quote::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'lead_id' => Lead::factory()->for($company)->create()->id,
    ]);

    return QuoteVersion::create(array_merge([
        'quote_id' => $quote->id,
        'version_letter' => 'A',
        'folio' => 'F-'.str()->random(8),
        'total' => 100,
        'status' => QuoteVersion::STATUS_EN_PROCESO,
    ], $attributes));
}

function visibleIds($component): array
{
    return $component->instance()->quotesVersions->pluck('id')->sort()->values()->all();
}

test('admin sees only their own quotes by default and all after clearing the seller filter', function () {
    $own = makeQuoteVersion($this->admin, $this->company);
    $sellers = makeQuoteVersion($this->seller, $this->company);

    $component = Livewire::actingAs($this->admin)->test(Index::class)
        ->set('company', $this->company->id);

    expect($component->get('userId'))->toBe($this->admin->id)
        ->and(visibleIds($component))->toBe([$own->id]);

    $component->set('userId', '');

    expect(visibleIds($component))->toBe([$own->id, $sellers->id]);
});

test('seller only sees their own quotes even when forcing another user filter', function () {
    $own = makeQuoteVersion($this->seller, $this->company);
    makeQuoteVersion($this->admin, $this->company);

    $component = Livewire::actingAs($this->seller)->test(Index::class)
        ->set('userId', $this->admin->id);

    expect(visibleIds($component))->toBe([$own->id]);
});

test('quotes are filtered by selected company', function () {
    $inCompany = makeQuoteVersion($this->admin, $this->company);
    $inOther = makeQuoteVersion($this->admin, $this->otherCompany);

    $component = Livewire::actingAs($this->admin)->test(Index::class)
        ->set('company', $this->company->id);
    expect(visibleIds($component))->toBe([$inCompany->id]);

    $component->set('company', $this->otherCompany->id);
    expect(visibleIds($component))->toBe([$inOther->id]);
});

test('quotes are filtered by status and date range', function () {
    $match = makeQuoteVersion($this->admin, $this->company, ['status' => QuoteVersion::STATUS_EXITOSA]);
    makeQuoteVersion($this->admin, $this->company, ['status' => QuoteVersion::STATUS_CANCELADA]);
    $old = makeQuoteVersion($this->admin, $this->company, ['status' => QuoteVersion::STATUS_EXITOSA]);
    $old->forceFill(['created_at' => now()->subMonth()])->saveQuietly();

    $component = Livewire::actingAs($this->admin)->test(Index::class)
        ->set('company', $this->company->id)
        ->set('status', QuoteVersion::STATUS_EXITOSA)
        ->set('dateFrom', now()->subWeek()->toDateString())
        ->set('dateTo', now()->toDateString());

    expect(visibleIds($component))->toBe([$match->id]);

    $component->call('clear');

    expect($component->get('userId'))->toBe($this->admin->id)
        ->and($component->get('status'))->toBe('')
        ->and(visibleIds($component))->toHaveCount(3);
});

test('per page only accepts 10, 20 or 30', function () {
    $component = Livewire::actingAs($this->admin)->test(Index::class)
        ->assertSet('perPage', 10)
        ->set('perPage', 20)
        ->assertSet('perPage', 20)
        ->set('perPage', 50)
        ->assertSet('perPage', 10);

    $component->assertOk();
});
