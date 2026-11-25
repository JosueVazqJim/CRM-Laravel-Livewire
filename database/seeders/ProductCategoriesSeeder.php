<?php

// ===== database/seeders/ProductCategoriesSeeder.php =====

namespace Database\Seeders;

use App\Models\Products\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Redes y conectividad',
            'Cómputo',
            'Almacenamiento',
            'Seguridad',
            'Energía',
            'Telecomunicaciones',
        ];

        foreach ($categories as $name) {
            ProductCategory::firstOrCreate(['name' => $name]);
        }
    }
}
