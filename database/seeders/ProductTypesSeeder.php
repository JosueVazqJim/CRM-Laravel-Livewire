<?php

// ===== database/seeders/ProductTypesSeeder.php =====

namespace Database\Seeders;

use App\Models\Products\ProductType;
use Illuminate\Database\Seeder;

class ProductTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Hardware',
            'Software',
            'Servicio',
            'Consumible',
            'Accesorio',
        ];

        foreach ($types as $name) {
            ProductType::firstOrCreate(['name' => $name]);
        }
    }
}
