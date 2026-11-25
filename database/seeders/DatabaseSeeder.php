<?php

namespace Database\Seeders;

use App\Models\Companies\Company;
use App\Models\Companies\CompanyAddress;
use App\Models\Companies\CompanyEmail;
use App\Models\Companies\CompanyPhone;
use App\Models\Users\User;
use App\Models\Users\UserAddress;
use App\Models\Users\UserPhone;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Datos demo para el portafolio.
     *
     * Usuarios de acceso (contraseña: "password"):
     *  - admin@demo.test    → rol admin
     *  - vendedor@demo.test → rol vendedor
     */
    public function run(): void
    {
        // Catálogos base
        $this->call([
            StateSeeder::class,
            MunicipalitySeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);

        // Empresas del corporativo (ficticias)
        $companies = Company::factory(3)
            ->has(CompanyPhone::factory()->count(2), 'companyPhones')
            ->has(CompanyAddress::factory()->count(1), 'companyAddress')
            ->has(CompanyEmail::factory()->count(1), 'companyEmails')
            ->create();

        // Usuarios demo con credenciales conocidas
        $admin = User::factory()
            ->has(UserPhone::factory()->count(1), 'phones')
            ->has(UserAddress::factory()->count(1), 'address')
            ->create([
                'first_name' => 'Admin',
                'paternal_surname' => 'Demo',
                'maternal_surname' => '',
                'email' => 'admin@demo.test',
            ]);
        $admin->syncRoles(['admin']);
        $admin->companies()->attach($companies->pluck('id'));

        $seller = User::factory()
            ->has(UserPhone::factory()->count(1), 'phones')
            ->has(UserAddress::factory()->count(1), 'address')
            ->create([
                'first_name' => 'Vendedor',
                'paternal_surname' => 'Demo',
                'maternal_surname' => '',
                'email' => 'vendedor@demo.test',
            ]);
        $seller->syncRoles(['vendedor']);
        $seller->companies()->attach($companies->first()->id);

        // Vendedores adicionales con datos aleatorios
        User::factory(3)
            ->has(UserPhone::factory()->count(1), 'phones')
            ->has(UserAddress::factory()->count(1), 'address')
            ->create()
            ->each(function (User $user) use ($companies): void {
                $user->syncRoles(['vendedor']);
                $user->companies()->attach($companies->random(rand(1, 2))->pluck('id'));
            });

        // Catálogo de productos
        $this->call([
            ProductTypesSeeder::class,
            ProductCategoriesSeeder::class,
            ProductTagsSeeder::class,
            ProductsSeeder::class, // debe ir al final, depende de los anteriores
        ]);

        // Leads y tablas relacionadas
        $this->call(LeadsSeeder::class);

        // Cotizaciones demo (alimentan el dashboard)
        $this->call(QuotesSeeder::class);
    }
}
