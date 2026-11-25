<?php

namespace Database\Seeders;

use App\Models\States\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('seeders/data/Estados.json');

        // a partir de la ruta, convertimos el json a un array asociativo de php
        // como por ejemplo:
        $states = json_decode(file_get_contents($jsonPath), true);

        DB::transaction(function () use ($states) {
            foreach ($states['response']['estado'] as $state) {
                State::create([
                    'name' => $state,
                ]);
            }
        });

    }
}
