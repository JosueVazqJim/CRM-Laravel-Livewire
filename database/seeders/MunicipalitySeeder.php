<?php

namespace Database\Seeders;

use App\Models\States\Municipality;
use App\Models\States\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MunicipalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = State::pluck('id', 'name')->toArray();

        // Directorio donde están los JSON de municipios
        $municipiosDir = database_path('seeders/data/municipios/');
        $files = glob($municipiosDir.'*.json');

        DB::transaction(function () use ($files, $states) {
            foreach ($files as $file) {
                // Extraer nombre del estado del nombre del archivo
                $stateName = $this->extractStateName($file);

                if (! isset($states[$stateName])) {
                    $this->command->warn("Estado no encontrado: {$stateName}");

                    continue;
                }

                $data = json_decode(file_get_contents($file), true);

                // Extraer el array de municipios
                $municipios = $data['response']['municipios'];

                foreach ($municipios as $municipioName) {
                    Municipality::create([
                        'state_id' => $states[$stateName],
                        'name' => $municipioName,
                    ]);
                }

                $this->command->info("Cargados municipios de: {$stateName}");
            }
        });
    }

    private function extractStateName($filePath)
    {
        $filename = pathinfo($filePath, PATHINFO_FILENAME);

        // Convierte "aguascalientes" a "Aguascalientes"
        return $filename;
    }
}
