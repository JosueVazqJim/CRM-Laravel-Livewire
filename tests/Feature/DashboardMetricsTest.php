<?php

use App\Livewire\Dash;
use App\Models\Companies\Company;
use App\Models\Leads\Lead;
use App\Models\Quotes\Quote;
use App\Models\Quotes\QuoteVersion;
use App\Models\Users\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->company = Company::factory()->create(['active' => true]);

    $this->admin = User::factory()->create();
    $this->admin->syncRoles(['admin']);
    $this->admin->companies()->attach($this->company->id);

    $this->seller = User::factory()->create();
    $this->seller->syncRoles(['vendedor']);
    $this->seller->companies()->attach($this->company->id);
});

function dashVersion(User $user, Company $company, array $attributes = [], ?QuoteVersion $source = null): QuoteVersion
{
    $quote = $source?->quote ?? Quote::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'lead_id' => Lead::factory()->for($company)->create()->id,
    ]);

    return QuoteVersion::create(array_merge([
        'quote_id' => $quote->id,
        'version_letter' => $source ? 'B' : 'A',
        'folio' => 'F-'.str()->random(8),
        'total' => 100,
        'status' => QuoteVersion::STATUS_EN_PROCESO,
        'source_version_id' => $source?->id,
    ], $attributes));
}

test('conversion rate counts won and lost quotes inside the window and the modal lists the same ones', function () {
    dashVersion($this->seller, $this->company, ['status' => QuoteVersion::STATUS_EXITOSA, 'total' => 300]);
    dashVersion($this->seller, $this->company, ['status' => QuoteVersion::STATUS_EXITOSA, 'total' => 100]);
    dashVersion($this->seller, $this->company, ['status' => QuoteVersion::STATUS_SIN_EXITO]);

    $outsideWindow = dashVersion($this->seller, $this->company, ['status' => QuoteVersion::STATUS_SIN_EXITO]);
    QuoteVersion::query()->whereKey($outsideWindow->id)->update(['updated_at' => now()->subDays(Dash::METRICS_WINDOW_DAYS + 1)]);

    $component = Livewire::actingAs($this->admin)->test(Dash::class);

    expect($component->instance()->conversionStats)->toBe([
        'won' => 2,
        'lost' => 1,
        'rate' => 66.7,
        'avg_ticket' => 200.0,
    ]);

    $component->call('openConversionModal', 'all')->assertSet('showConversionModal', true);
    expect($component->instance()->conversionModalList())->toHaveCount(3);

    $component->call('openConversionModal', QuoteVersion::STATUS_SIN_EXITO);
    expect($component->instance()->conversionModalList())->toHaveCount(1);

    $component->call('openConversionModal', 'invalid');
    expect($component->get('conversionModalFilter'))->toBe('all');
});

test('open quotes only count the current version of each revision tree', function () {
    $root = dashVersion($this->seller, $this->company, ['total' => 500]);
    dashVersion($this->seller, $this->company, ['total' => 400], source: $root);
    dashVersion($this->seller, $this->company, ['status' => QuoteVersion::STATUS_EN_VALIDACION, 'total' => 50]);
    dashVersion($this->seller, $this->company, ['status' => QuoteVersion::STATUS_CANCELADA]);

    $component = Livewire::actingAs($this->admin)->test(Dash::class);

    expect($component->instance()->openQuotesStats)->toBe(['count' => 2, 'amount' => 450.0])
        ->and($component->instance()->openQuotesModalList())->toHaveCount(2);
});

test('sellers only see their own quotes in the metrics and never the ranking', function () {
    dashVersion($this->seller, $this->company, ['status' => QuoteVersion::STATUS_EXITOSA]);
    dashVersion($this->admin, $this->company, ['status' => QuoteVersion::STATUS_SIN_EXITO]);

    $component = Livewire::actingAs($this->seller)->test(Dash::class);

    expect($component->instance()->conversionStats['won'])->toBe(1)
        ->and($component->instance()->conversionStats['lost'])->toBe(0)
        ->and($component->instance()->sellerRanking)->toBeEmpty();

    $component->assertDontSee('Ranking de vendedores');
});

test('admin general view shows the seller ranking', function () {
    dashVersion($this->seller, $this->company, ['status' => QuoteVersion::STATUS_EXITOSA, 'total' => 1000]);
    dashVersion($this->seller, $this->company, ['status' => QuoteVersion::STATUS_SIN_EXITO]);

    $ranking = Livewire::actingAs($this->admin)->test(Dash::class)
        ->call('setViewMode', 'general')
        ->assertSee('Ranking de vendedores')
        ->instance()->sellerRanking;

    expect($ranking)->toHaveCount(1)
        ->and($ranking->first()['user']->id)->toBe($this->seller->id)
        ->and($ranking->first()['won'])->toBe(1)
        ->and($ranking->first()['rate'])->toEqual(50);
});

test('sales per month always returns six months and fills empty ones with zero', function () {
    Carbon::setTestNow('2026-11-15 12:00:00');

    dashVersion($this->seller, $this->company, ['status' => QuoteVersion::STATUS_EXITOSA, 'total' => 250, 'closed_at' => '2026-09-10']);
    dashVersion($this->seller, $this->company, ['status' => QuoteVersion::STATUS_EXITOSA, 'total' => 150, 'closed_at' => '2026-09-20']);
    dashVersion($this->seller, $this->company, ['status' => QuoteVersion::STATUS_EXITOSA, 'total' => 999, 'closed_at' => '2026-04-01']);

    $chart = Livewire::actingAs($this->admin)->test(Dash::class)->get('salesPerMonthChart');

    expect($chart['data']['labels'])->toBe(['2026-06', '2026-07', '2026-08', '2026-09', '2026-10', '2026-11'])
        ->and($chart['data']['datasets'][0]['data'])->toBe([0, 0, 0, 400, 0, 0]);

    Carbon::setTestNow();
});
