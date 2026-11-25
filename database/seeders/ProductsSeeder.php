<?php

// ===== database/seeders/ProductsSeeder.php =====

namespace Database\Seeders;

use App\Models\Companies\Company;
use App\Models\Products\Product;
use App\Models\Products\ProductCategory;
use App\Models\Products\ProductSerial;
use App\Models\Products\ProductTag;
use App\Models\Products\ProductType;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $hardware = ProductType::where('name', 'Hardware')->first();
        $redes = ProductCategory::where('name', 'Redes y conectividad')->first();
        $tagNew = ProductTag::where('name', 'Nuevo')->first();

        if (! $company || ! $hardware || ! $redes) {
            $this->command->warn('Faltan datos de Company, ProductType o ProductCategory. Ejecuta sus seeders primero.');

            return;
        }

        // Producto 1
        $switch = Product::firstOrCreate(
            ['sku' => 'SWITCH-24P-001'],
            [
                'company_id' => $company->id,
                'product_type_id' => $hardware->id,
                'product_category_id' => $redes->id,
                'name' => 'Switch 24 puertos PoE',
                'currency' => 'USD',
                'price_regular' => 350.00,
                'price_min' => 320.00,
                'price_max' => 380.00,
                'installation_cost' => 50.00,
                'color' => '#1A1A1A',
                'short_description' => 'Switch administrable 24 puertos PoE+ Gigabit',
                'long_description' => 'Switch administrable de 24 puertos PoE+ Gigabit con 4 uplinks SFP. Ideal para instalaciones medianas.',
                'active' => true,
            ]
        );
        $switch->tags()->syncWithoutDetaching([$tagNew->id]);

        // Especificaciones
        $switch->specifications()->createMany([
            ['spec_key' => 'Puertos',     'spec_value' => '24 x GE PoE+ + 4 x SFP', 'sort_order' => 1],
            ['spec_key' => 'Potencia PoE', 'spec_value' => '370W total',               'sort_order' => 2],
            ['spec_key' => 'Switching',   'spec_value' => '56 Gbps',                  'sort_order' => 3],
        ]);

        // Seriales de ejemplo (3 unidades físicas)
        $serials = ['SN-SW-001', 'SN-SW-002', 'SN-SW-003'];
        foreach ($serials as $sn) {
            ProductSerial::firstOrCreate(
                ['serial_number' => $sn],
                [
                    'product_id' => $switch->id,
                    'status' => 'in_stock',
                    'purchase_price' => 280.00,
                    'purchased_at' => now()->subDays(30),
                    'entered_stock_at' => now()->subDays(25),
                ]
            );
        }

        // Producto 2
        $ap = Product::firstOrCreate(
            ['sku' => 'AP-AX3000-001'],
            [
                'company_id' => $company->id,
                'product_type_id' => $hardware->id,
                'product_category_id' => $redes->id,
                'name' => 'Access Point WiFi 6 AX3000',
                'currency' => 'USD',
                'price_regular' => 180.00,
                'price_min' => 160.00,
                'price_max' => 200.00,
                'installation_cost' => 30.00,
                'color' => '#FFFFFF',
                'short_description' => 'Access Point Wi-Fi 6 de doble banda AX3000',
                'long_description' => 'Punto de acceso Wi-Fi 6 con tecnología MU-MIMO y OFDMA. Cobertura de hasta 300m² en interiores.',
                'active' => true,
            ]
        );
        $ap->tags()->syncWithoutDetaching([$tagNew->id]);
    }
}

// ===== database/seeders/DatabaseSeeder.php (fragmento a agregar) =====
// Agrega estas líneas en el método run() de tu DatabaseSeeder existente:
//
//  $this->call([
//      ProductTypesSeeder::class,
//      ProductCategoriesSeeder::class,
//      ProductTagsSeeder::class,
//      ProductsSeeder::class,   // debe ir al final, depende de los anteriores
//  ]);
