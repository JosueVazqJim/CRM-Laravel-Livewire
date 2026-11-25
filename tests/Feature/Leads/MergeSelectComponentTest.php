<?php

use App\Livewire\Leads\MergeSelect;
use App\Models\Companies\Company;
use App\Models\Leads\Lead;
use App\Models\Users\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->company = Company::factory()->create();
});

test('a vendedor without access to the base lead gets a 403', function () {
    $vendedor = User::factory()->create();
    $lead = Lead::factory()->for($this->company)->create();

    $this->actingAs($vendedor)
        ->get(route('leads.merge.select', $lead))
        ->assertForbidden();
});

test('candidates are scoped to the same company and exclude the base lead', function () {
    $admin = User::factory()->create();
    $admin->syncRoles(['admin']);

    $lead = Lead::factory()->for($this->company)->create(['name_company' => 'Lead Base']);
    $sameCompanyLead = Lead::factory()->for($this->company)->create(['name_company' => 'Empresa Hermana']);
    $otherCompanyLead = Lead::factory()->create(['name_company' => 'Empresa Ajena']);

    $this->actingAs($admin);

    Livewire::test(MergeSelect::class, ['lead' => $lead])
        ->assertSee('Empresa Hermana')
        ->assertDontSee('Empresa Ajena')
        ->assertDontSeeHtml('value="'.$lead->id.'"');
});

test('search filters candidates by name_company', function () {
    $admin = User::factory()->create();
    $admin->syncRoles(['admin']);

    $lead = Lead::factory()->for($this->company)->create();
    $match = Lead::factory()->for($this->company)->create(['name_company' => 'Acme Corp']);
    $noMatch = Lead::factory()->for($this->company)->create(['name_company' => 'Globex Inc']);

    $this->actingAs($admin);

    Livewire::test(MergeSelect::class, ['lead' => $lead])
        ->set('search', 'Acme')
        ->assertSee('Acme Corp')
        ->assertDontSee('Globex Inc');
});

test('the continue button links to the merge wizard with the base lead plus selected candidates', function () {
    $admin = User::factory()->create();
    $admin->syncRoles(['admin']);

    $lead = Lead::factory()->for($this->company)->create();
    $candidate = Lead::factory()->for($this->company)->create();

    $this->actingAs($admin);

    Livewire::test(MergeSelect::class, ['lead' => $lead])
        ->set('selectedLeadIds', [$candidate->id])
        ->assertSeeHtml(route('leads.merge')."?ids={$lead->id},{$candidate->id}");
});
