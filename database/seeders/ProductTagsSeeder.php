<?php

// ===== database/seeders/ProductTagsSeeder.php =====

namespace Database\Seeders;

use App\Models\Products\ProductTag;
use Illuminate\Database\Seeder;

class ProductTagsSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'Nuevo',
            'Destacado',
            'Oferta',
            'Sin stock',
            'Importado',
            'Nacional',
        ];

        foreach ($tags as $name) {
            ProductTag::firstOrCreate(['name' => $name]);
        }
    }
}
