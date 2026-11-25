<?php

namespace Database\Seeders;

use App\Models\Companies\Company;
use App\Models\Leads\BillingAddress;
use App\Models\Leads\BillingInfo;
use App\Models\Leads\ChannelContact;
use App\Models\Leads\ContactPerson;
use App\Models\Leads\Lead;
use App\Models\Leads\LeadActivity;
use App\Models\Leads\LeadUserAccess;
use App\Models\Users\User;
use Illuminate\Database\Seeder;

class LeadsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $companies = Company::all();

        // Crear algunas direcciones de facturación con su info (user_id es unique)
        $billingInfos = collect();
        $availableUsers = $users->shuffle()->take(min(5, $users->count()));
        foreach ($availableUsers as $user) {
            $billingAddress = BillingAddress::factory()->create();

            $billingInfos->push(BillingInfo::factory()->create([
                'address_id' => $billingAddress->id,
            ]));
        }

        // Crear 20 leads con relaciones
        Lead::factory(20)
            ->recycle($companies)
            ->recycle($users)
            ->create([
                'billing_id' => fn () => $billingInfos->random()->id,
            ])
            ->each(function (Lead $lead) use ($users) {
                // 1-3 contactos por lead, cada uno con 1-2 canales
                ContactPerson::factory(rand(1, 3))
                    ->create(['lead_id' => $lead->id])
                    ->each(function (ContactPerson $contact) {
                        ChannelContact::factory(rand(1, 2))
                            ->create(['lead_contact_id' => $contact->id]);
                    });

                // 3-7 actividades (combinando pasadas y futuras)
                // El factory ya maneja el estado is_completed y fechas programadas
                LeadActivity::factory(rand(3, 7))
                    ->create(['lead_id' => $lead->id]);

                // 1-3 accesos de usuario
                $accessUsers = $users->random(min(rand(1, 3), $users->count()));
                foreach ($accessUsers as $user) {
                    LeadUserAccess::factory()->create([
                        'lead_id' => $lead->id,
                        'user_id' => $user->id,
                    ]);
                }
            });
    }
}
