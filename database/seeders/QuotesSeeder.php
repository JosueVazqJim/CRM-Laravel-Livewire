<?php

namespace Database\Seeders;

use App\Models\Leads\Lead;
use App\Models\Products\Product;
use App\Models\Quotes\Quote;
use App\Models\Quotes\QuoteExchangeRate;
use App\Models\Quotes\QuoteItem;
use App\Models\Quotes\QuoteVersion;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Cotizaciones demo repartidas en los últimos 6 meses para poblar el dashboard.
 *
 * Usa los mismos helpers del modelo que la aplicación (letras de versión y folio),
 * guarda el snapshot de tipo de cambio e incluye algunas revisiones.
 */
class QuotesSeeder extends Seeder
{
    /** Estado final => peso relativo. */
    private const STATUS_WEIGHTS = [
        QuoteVersion::STATUS_EXITOSA => 35,
        QuoteVersion::STATUS_SIN_EXITO => 20,
        QuoteVersion::STATUS_EN_PROCESO => 25,
        QuoteVersion::STATUS_EN_VALIDACION => 10,
        QuoteVersion::STATUS_CANCELADA => 10,
    ];

    public function run(): void
    {
        $products = Product::query()->where('active', true)->get();
        $leads = Lead::query()->whereNotNull('company_id')->whereNotNull('user_id')->get();

        if ($products->isEmpty() || $leads->isEmpty()) {
            $this->command?->warn('QuotesSeeder: se necesitan productos y leads. Ejecuta sus seeders primero.');

            return;
        }

        foreach (range(1, 45) as $i) {
            $lead = $leads->random();
            $createdAt = Carbon::instance(fake()->dateTimeBetween('-6 months', '-1 day'));
            $rate = fake()->randomFloat(4, 16.8, 20.5);

            $quote = Quote::create([
                'lead_id' => $lead->id,
                'user_id' => $lead->user_id,
                'company_id' => $lead->company_id,
                'base_currency' => 'USD',
                'parent_quote_id' => null,
            ]);
            $this->stamp($quote, $createdAt, $createdAt);

            $companyProducts = $products->where('company_id', $lead->company_id);
            $catalog = $companyProducts->isNotEmpty() ? $companyProducts : $products;

            $finalStatus = $this->randomStatus();

            // ~30 % pasa por una revisión antes de llegar a su estado final
            if (fake()->boolean(30)) {
                $root = $this->createVersion($quote, null, QuoteVersion::STATUS_EN_PROCESO, $catalog, $rate, $createdAt, $createdAt->copy()->addDays(2));
                $revisionAt = $createdAt->copy()->addDays(rand(3, 10));
                $this->createVersion($quote, $root, $finalStatus, $catalog, $rate, $revisionAt, $this->closingDate($revisionAt, $finalStatus));
            } else {
                $this->createVersion($quote, null, $finalStatus, $catalog, $rate, $createdAt, $this->closingDate($createdAt, $finalStatus));
            }
        }
    }

    private function createVersion(
        Quote $quote,
        ?QuoteVersion $source,
        string $status,
        Collection $catalog,
        float $rate,
        CarbonInterface $createdAt,
        CarbonInterface $updatedAt,
    ): QuoteVersion {
        $letter = $source
            ? $source->nextChildVersionLetter()
            : QuoteVersion::nextRootVersionLetter($quote->id);

        // El folio incluye la fecha (now()); se fija a la fecha de la versión demo
        Carbon::setTestNow($createdAt);
        $folio = QuoteVersion::generateFolio($quote, $letter);
        Carbon::setTestNow();

        $version = QuoteVersion::create([
            'quote_id' => $quote->id,
            'version_letter' => $letter,
            'folio' => $folio,
            'status' => $status,
            // Las exitosas se cierran en la fecha de su último cambio
            'closed_at' => $status === QuoteVersion::STATUS_EXITOSA ? $updatedAt : null,
            'source_version_id' => $source?->id,
        ]);

        // Snapshot de tipo de cambio, igual que QuoteForm::saveExchangeRateSnapshots()
        foreach ([['USD', 'USD', 1], ['USD', 'MXN', $rate]] as [$from, $to, $value]) {
            QuoteExchangeRate::create([
                'quote_version_id' => $version->id,
                'from_currency' => $from,
                'to_currency' => $to,
                'rate' => $value,
            ]);
        }

        $subtotal = 0.0;

        foreach ($catalog->random(min($catalog->count(), rand(1, 3))) as $product) {
            $originalPrice = (float) ($product->price_regular ?: $product->price_max ?: 100);
            $priceUsd = $product->currency === 'MXN' ? $originalPrice / $rate : $originalPrice;

            $item = QuoteItem::create([
                'quote_version_id' => $version->id,
                'product_id' => $product->id,
                'quantity' => rand(1, 10),
                'unit_price_original' => $originalPrice,
                'currency_original' => $product->currency,
                'unit_price_converted' => $priceUsd,
                'exchange_rate_used' => $product->currency === 'MXN' ? $rate : 1,
                'custom_price' => false,
                'unit_price_converted_custom' => $priceUsd,
                'discount' => fake()->randomElement([0, 0, 0, 5, 10]),
            ]);

            $subtotal += $item->lineTotalWithDiscount();
        }

        $tax = round($subtotal * QuoteVersion::TAX_RATE, 4);

        $version->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
        ]);

        $this->stamp($version, $createdAt, $updatedAt);

        return $version;
    }

    private function closingDate(CarbonInterface $from, string $status): CarbonInterface
    {
        // Las abiertas quedan con su fecha de creación (algunas aparecerán como "sin movimiento")
        if (in_array($status, [QuoteVersion::STATUS_EN_PROCESO, QuoteVersion::STATUS_EN_VALIDACION], true)) {
            return $from;
        }

        return Carbon::instance(fake()->dateTimeBetween($from, 'now'));
    }

    private function randomStatus(): string
    {
        $pick = rand(1, array_sum(self::STATUS_WEIGHTS));

        foreach (self::STATUS_WEIGHTS as $status => $weight) {
            $pick -= $weight;

            if ($pick <= 0) {
                return $status;
            }
        }

        return QuoteVersion::STATUS_EN_PROCESO;
    }

    /**
     * Ajusta las fechas sin disparar eventos del modelo (solo para datos demo).
     */
    private function stamp(Quote|QuoteVersion $model, CarbonInterface $createdAt, CarbonInterface $updatedAt): void
    {
        $model::query()->whereKey($model->getKey())->update([
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);
    }
}
