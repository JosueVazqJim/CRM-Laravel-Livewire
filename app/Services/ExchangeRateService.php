<?php

namespace App\Services;

use App\Models\Quotes\ExchangeRate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ExchangeRateService
{
    protected array $supportedCurrencies = ['USD', 'MXN'];

    protected string $baseCurrency = 'USD';

    // Configuración de Banxico
    protected string $banxicoUrl = 'https://www.banxico.org.mx/SieAPIRest/service/v1/series/SF43718/datos/oportuno';

    public function ensureFreshRates(): void
    {
        $latest = ExchangeRate::orderByDesc('valid_at')->first();

        // Verificar si la última actualización fue hoy
        $isFromToday = $latest && $latest->valid_at->isToday();

        if ($isFromToday) {
            return;
        }

        Cache::lock('exchange_rates_update', 10)->get(function () {
            $latest = ExchangeRate::orderByDesc('valid_at')->first();

            // Doble verificación dentro del lock
            if ($latest && $latest->valid_at->isToday()) {
                return;
            }

            $this->updateRates();
        });
    }

    private function updateRates(): void
    {
        $ratesFromApi = $this->fetchFromApi();
        $now = now();

        foreach ($this->supportedCurrencies as $currency) {
            $rate = match ($currency) {
                $this->baseCurrency => 1,
                default => $ratesFromApi[$currency] ?? null,
            };

            if (is_null($rate)) {
                throw new \Exception("Missing rate for {$currency}");
            }

            ExchangeRate::create([
                'base_currency' => $this->baseCurrency,
                'target_currency' => $currency,
                'rate' => $rate,
                'valid_at' => $now,
            ]);
        }
    }

    private function fetchFromApi(): array
    {
        $response = Http::withHeaders([
            'Bmx-Token' => ENV('BANXICO_TOKEN'),
            'Accept' => 'application/json',
        ])->get($this->banxicoUrl);

        if (! $response->successful()) {
            throw new \Exception('Error fetching exchange rates from Banxico');
        }

        $data = $response->json();

        // Navegación del JSON (sí, viene anidado como matrioshka rusa)
        $series = $data['bmx']['series'][0]['datos'] ?? null;

        if (! $series || count($series) === 0) {
            throw new \Exception('No data returned from Banxico API');
        }

        // Tomamos el último valor disponible
        $latest = end($series);

        $rate = $latest['dato'] ?? null;

        if (! $rate || $rate === 'N/E') {
            throw new \Exception('Invalid exchange rate value from Banxico');
        }

        return [
            'MXN' => (float) $rate,
            'USD' => 1.0,
        ];
    }
}
