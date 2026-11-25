<?php

use App\Livewire\Leads\Merge;
use App\Models\Companies\Company;
use App\Models\Leads\BillingAddress;
use App\Models\Leads\BillingInfo;
use App\Models\Leads\ChannelContact;
use App\Models\Leads\ContactPerson;
use App\Models\Leads\Lead;
use App\Models\Quotes\Quote;
use App\Models\Users\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->company = Company::factory()->create();
});

test('a vendedor without access to one of the leads gets a 403', function () {
    $vendedor = User::factory()->create();

    $ownedLead = Lead::factory()->for($this->company)->create(['user_id' => $vendedor->id]);
    $notOwnedLead = Lead::factory()->for($this->company)->create();

    $response = $this->actingAs($vendedor)
        ->get('/leads/fusionar?ids='.$ownedLead->id.','.$notOwnedLead->id);

    $response->assertForbidden();
});

test('an admin can open the merge screen for any accessible set of leads', function () {
    $admin = User::factory()->create();
    $admin->syncRoles(['admin']);

    $leadA = Lead::factory()->for($this->company)->create();
    $leadB = Lead::factory()->for($this->company)->create();

    $response = $this->actingAs($admin)
        ->get('/leads/fusionar?ids='.$leadA->id.','.$leadB->id);

    $response->assertOk();
});

test('fewer than two valid ids in the query string aborts with 400', function () {
    $admin = User::factory()->create();
    $admin->syncRoles(['admin']);

    $leadA = Lead::factory()->for($this->company)->create();

    $response = $this->actingAs($admin)->get('/leads/fusionar?ids='.$leadA->id);

    $response->assertStatus(400);
});

test('an id that does not exist aborts with 404', function () {
    $admin = User::factory()->create();
    $admin->syncRoles(['admin']);

    $leadA = Lead::factory()->for($this->company)->create();

    $response = $this->actingAs($admin)->get('/leads/fusionar?ids='.$leadA->id.',999999');

    $response->assertNotFound();
});

test('leads from different companies abort with 400', function () {
    $admin = User::factory()->create();
    $admin->syncRoles(['admin']);

    $leadA = Lead::factory()->for($this->company)->create();
    $leadB = Lead::factory()->create();

    $response = $this->actingAs($admin)->get('/leads/fusionar?ids='.$leadA->id.','.$leadB->id);

    $response->assertStatus(400);
});

test('a successful merge redirects to the survivor show page', function () {
    $admin = User::factory()->create();
    $admin->syncRoles(['admin']);

    $leadA = Lead::factory()->for($this->company)->create();
    $leadB = Lead::factory()->for($this->company)->create();

    $this->actingAs($admin);

    Livewire::withQueryParams(['ids' => "{$leadA->id},{$leadB->id}"])
        ->test(Merge::class)
        ->set('survivorId', $leadA->id)
        ->call('merge')
        ->assertRedirect(route('leads.show', $leadA));

    expect(Lead::find($leadB->id))->toBeNull();
});

test('renders the comparison screen with quotes, billing and contacts on every lead', function () {
    $admin = User::factory()->create();
    $admin->syncRoles(['admin']);

    $addressA = BillingAddress::factory()->create(['street' => 'Calle A']);
    $billingA = BillingInfo::factory()->create(['address_id' => $addressA->id, 'business_name' => 'Empresa A']);
    $leadA = Lead::factory()->for($this->company)->create(['name_company' => 'Lead A', 'billing_id' => $billingA->id]);
    $contactA = ContactPerson::factory()->create(['lead_id' => $leadA->id, 'name_contact' => 'Contacto A']);
    ChannelContact::factory()->create(['lead_contact_id' => $contactA->id, 'type' => 'email', 'channel' => 'a@example.com']);
    Quote::factory()->create(['company_id' => $this->company->id, 'lead_id' => $leadA->id]);

    $addressB = BillingAddress::factory()->create(['street' => 'Calle B']);
    $billingB = BillingInfo::factory()->create(['address_id' => $addressB->id, 'business_name' => 'Empresa B']);
    $leadB = Lead::factory()->for($this->company)->create(['name_company' => 'Lead B', 'billing_id' => $billingB->id]);
    $contactB = ContactPerson::factory()->create(['lead_id' => $leadB->id, 'name_contact' => 'Contacto B']);
    ChannelContact::factory()->create(['lead_contact_id' => $contactB->id, 'type' => 'phone', 'channel' => '555-0100']);
    Quote::factory()->create(['company_id' => $this->company->id, 'lead_id' => $leadB->id]);

    $response = $this->actingAs($admin)
        ->get('/leads/fusionar?ids='.$leadA->id.','.$leadB->id);

    $response->assertOk()
        ->assertSee('Lead A')
        ->assertSee('Lead B')
        ->assertSee('Contacto A')
        ->assertSee('Contacto B')
        ->assertSee('Calle A')
        ->assertSee('Calle B');
});

test('merging without keeping any contact is rejected when leads have contacts', function () {
    $admin = User::factory()->create();
    $admin->syncRoles(['admin']);

    $leadA = Lead::factory()->for($this->company)->create();
    $leadB = Lead::factory()->for($this->company)->create();
    ContactPerson::factory()->create(['lead_id' => $leadA->id]);

    $this->actingAs($admin);

    Livewire::withQueryParams(['ids' => "{$leadA->id},{$leadB->id}"])
        ->test(Merge::class)
        ->set('survivorId', $leadA->id)
        ->set('contactPeopleIdsToKeep', [])
        ->call('merge')
        ->assertNoRedirect();

    expect(Lead::find($leadB->id))->not->toBeNull();
});
