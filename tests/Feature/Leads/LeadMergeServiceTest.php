<?php

use App\Models\Companies\Company;
use App\Models\Leads\BillingAddress;
use App\Models\Leads\BillingInfo;
use App\Models\Leads\ChannelContact;
use App\Models\Leads\ContactPerson;
use App\Models\Leads\Lead;
use App\Models\Leads\LeadActivity;
use App\Models\Leads\LeadNote;
use App\Models\Leads\LeadUserAccess;
use App\Models\Quotes\Quote;
use App\Models\Users\User;
use App\Services\Leads\LeadMergeService;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->company = Company::factory()->create();
    $this->service = app(LeadMergeService::class);
});

test('merges two leads and keeps chosen field values on the survivor', function () {
    $leadA = Lead::factory()->for($this->company)->create(['name_company' => 'Lead A', 'urgency_level' => 'baja']);
    $leadB = Lead::factory()->for($this->company)->create(['name_company' => 'Lead B', 'urgency_level' => 'alta']);

    $survivor = $this->service->merge(
        leadIds: [$leadA->id, $leadB->id],
        survivorId: $leadA->id,
        fieldChoices: ['name_company' => $leadA->id, 'urgency_level' => $leadB->id],
        billingLeadId: $leadA->id,
        contactPeopleIdsToKeep: [],
    );

    expect($survivor->id)->toBe($leadA->id)
        ->and($survivor->name_company)->toBe('Lead A')
        ->and($survivor->urgency_level)->toBe('alta');
});

test('reassigns quotes from loser leads to the survivor', function () {
    $leadA = Lead::factory()->for($this->company)->create();
    $leadB = Lead::factory()->for($this->company)->create();
    $quote = Quote::factory()->create(['company_id' => $this->company->id, 'lead_id' => $leadB->id]);

    $this->service->merge([$leadA->id, $leadB->id], $leadA->id, [], $leadA->id, []);

    expect($quote->fresh()->lead_id)->toBe($leadA->id);
});

test('reassigns lead_activities and lead_notes to the survivor', function () {
    $leadA = Lead::factory()->for($this->company)->create();
    $leadB = Lead::factory()->for($this->company)->create();
    $activity = LeadActivity::factory()->create(['lead_id' => $leadB->id]);
    $note = LeadNote::factory()->create(['lead_id' => $leadB->id]);

    $this->service->merge([$leadA->id, $leadB->id], $leadA->id, [], $leadA->id, []);

    expect($activity->fresh()->lead_id)->toBe($leadA->id)
        ->and($note->fresh()->lead_id)->toBe($leadA->id);
});

test('keeps selected contact_people from any merged lead and reassigns them to the survivor', function () {
    $leadA = Lead::factory()->for($this->company)->create();
    $leadB = Lead::factory()->for($this->company)->create();
    $contactA = ContactPerson::factory()->create(['lead_id' => $leadA->id]);
    $contactB = ContactPerson::factory()->create(['lead_id' => $leadB->id]);

    $this->service->merge([$leadA->id, $leadB->id], $leadA->id, [], $leadA->id, [$contactA->id, $contactB->id]);

    expect($contactA->fresh()->lead_id)->toBe($leadA->id)
        ->and($contactB->fresh()->lead_id)->toBe($leadA->id);
});

test('deletes unselected contact_people and their channel_contact rows', function () {
    $leadA = Lead::factory()->for($this->company)->create();
    $leadB = Lead::factory()->for($this->company)->create();
    $keptContact = ContactPerson::factory()->create(['lead_id' => $leadA->id]);
    $discardedContact = ContactPerson::factory()->create(['lead_id' => $leadB->id]);
    $channel = ChannelContact::factory()->create(['lead_contact_id' => $discardedContact->id]);

    $this->service->merge([$leadA->id, $leadB->id], $leadA->id, [], $leadA->id, [$keptContact->id]);

    expect(ContactPerson::find($discardedContact->id))->toBeNull()
        ->and(ChannelContact::find($channel->id))->toBeNull()
        ->and(ContactPerson::find($keptContact->id))->not->toBeNull();
});

test('applies the chosen billing pair without touching billing_info or billing_address of unselected leads', function () {
    $addressA = BillingAddress::factory()->create(['street' => 'Calle A']);
    $billingA = BillingInfo::factory()->create(['address_id' => $addressA->id, 'business_name' => 'Empresa A']);
    $leadA = Lead::factory()->for($this->company)->create(['billing_id' => $billingA->id]);

    $addressB = BillingAddress::factory()->create(['street' => 'Calle B']);
    $billingB = BillingInfo::factory()->create(['address_id' => $addressB->id, 'business_name' => 'Empresa B']);
    $leadB = Lead::factory()->for($this->company)->create(['billing_id' => $billingB->id]);

    $survivor = $this->service->merge([$leadA->id, $leadB->id], $leadA->id, [], $leadB->id, []);

    expect($survivor->billing_id)->toBe($billingB->id)
        ->and(BillingInfo::find($billingA->id))->not->toBeNull()
        ->and(BillingAddress::find($addressA->id))->not->toBeNull();
});

test('dedupes lead_user_access by user keeping the highest permission_level', function () {
    $leadA = Lead::factory()->for($this->company)->create();
    $leadB = Lead::factory()->for($this->company)->create();
    $user = User::factory()->create();

    LeadUserAccess::factory()->create(['lead_id' => $leadA->id, 'user_id' => $user->id, 'permission_level' => 'view']);
    LeadUserAccess::factory()->create(['lead_id' => $leadB->id, 'user_id' => $user->id, 'permission_level' => 'manage']);

    $this->service->merge([$leadA->id, $leadB->id], $leadA->id, [], $leadA->id, []);

    $rows = LeadUserAccess::where('lead_id', $leadA->id)->where('user_id', $user->id)->get();

    expect($rows)->toHaveCount(1)
        ->and($rows->first()->permission_level)->toBe('manage');
});

test('hard deletes the losing leads after merge', function () {
    $leadA = Lead::factory()->for($this->company)->create();
    $leadB = Lead::factory()->for($this->company)->create();

    $this->service->merge([$leadA->id, $leadB->id], $leadA->id, [], $leadA->id, []);

    expect(Lead::find($leadA->id))->not->toBeNull()
        ->and(Lead::find($leadB->id))->toBeNull();
});

test('throws when leads belong to different companies', function () {
    $leadA = Lead::factory()->create();
    $leadB = Lead::factory()->create();

    expect(fn () => $this->service->merge([$leadA->id, $leadB->id], $leadA->id, [], $leadA->id, []))
        ->toThrow(InvalidArgumentException::class);
});

test('throws when survivor_id is not among the selected lead ids', function () {
    $leadA = Lead::factory()->for($this->company)->create();
    $leadB = Lead::factory()->for($this->company)->create();
    $other = Lead::factory()->for($this->company)->create();

    expect(fn () => $this->service->merge([$leadA->id, $leadB->id], $other->id, [], $leadA->id, []))
        ->toThrow(InvalidArgumentException::class);
});

test('throws when a field choice points to a lead outside the selected set', function () {
    $leadA = Lead::factory()->for($this->company)->create();
    $leadB = Lead::factory()->for($this->company)->create();
    $other = Lead::factory()->for($this->company)->create();

    expect(fn () => $this->service->merge([$leadA->id, $leadB->id], $leadA->id, ['name_company' => $other->id], $leadA->id, []))
        ->toThrow(InvalidArgumentException::class);
});

test('throws when fewer than two leads are provided', function () {
    $leadA = Lead::factory()->for($this->company)->create();

    expect(fn () => $this->service->merge([$leadA->id], $leadA->id, [], $leadA->id, []))
        ->toThrow(InvalidArgumentException::class);
});
