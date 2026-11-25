<?php

namespace App\Livewire\Forms\Quotes;

use AllowDynamicProperties;
use App\Models\Products\Product;
use App\Models\Quotes\Quote;
use App\Models\Quotes\QuoteConcept;
use App\Models\Quotes\QuoteExchangeRate;
use App\Models\Quotes\QuoteItem;
use App\Models\Quotes\QuoteNote;
use App\Models\Quotes\QuoteVersion;
use Illuminate\Support\Facades\DB;
use Livewire\Form;

#[AllowDynamicProperties]
class QuoteForm extends Form
{
    public int $quote_id = 0;

    public string $version_letter = '';

    public string $folio = '';

    public int $company_id = 0;

    public int $lead_id = 0;

    public string $base_currency = 'USD';

    public float $subtotal = 0;

    public float $tax = 0;

    public ?float $shipping_price = null;

    public float $total = 0;

    public string $status = '';

    public ?int $source_version_id = null;

    public string $source_version_letter = '';

    public array $selectedProducts = [];

    public array $items = [];

    public array $currents = [];

    public array $notes = [];

    public array $concepts = [];

    // Propiedades adicionales para el show
    public array $exchangeRatesSnapshot = [];

    public array $formattedItems = [];

    public array $timestamps = [];

    public function rules()
    {
        return [
            'company_id' => 'required|exists:companies,id',
            'shipping_price' => 'nullable|numeric|min:0',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price_converted' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|array',
            'notes.*.note' => 'nullable|string|max:10000',
        ];
    }

    public function messages()
    {
        return [
            'items' => 'Debes agregar al menos un producto o concepto.',
            'shipping_price.numeric' => 'El costo de envío debe ser un número',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (empty($this->items) && empty($this->concepts)) {
                $validator->errors()->add(
                    'items',
                    'Debes agregar al menos un producto o concepto a la cotización.'
                );
            }
        });
    }

    /**
     * Guardar la cotización completa
     */
    public function save(): QuoteVersion
    {
        return DB::transaction(function () {
            // 1. Determinar parent_quote_id (solo revisión: source_version_id está seteado)
            $parentQuoteId = null;
            if ($this->source_version_id) {
                $sourceVersion = QuoteVersion::find($this->source_version_id);
                $parentQuoteId = $sourceVersion?->quote_id;
            }

            // 2. Crear el Quote principal
            $quote = Quote::create([
                'user_id' => auth()->id(),
                'lead_id' => $this->lead_id,
                'company_id' => $this->company_id,
                'base_currency' => $this->base_currency,
                'parent_quote_id' => $parentQuoteId,
            ]);

            // 3. Determinar la letra de versión

            $versionLetter = $this->source_version_id
                ? QuoteVersion::findOrFail($this->source_version_id)->nextChildVersionLetter()
                : QuoteVersion::nextRootVersionLetter($quote->id);

            // 4. Generar el folio (depende del ID)
            $folio = QuoteVersion::generateFolio($quote, $versionLetter);

            // 5. Crear la versión
            $quoteVersion = QuoteVersion::create([
                'quote_id' => $quote->id,
                'version_letter' => $versionLetter,
                'folio' => $folio,
                'subtotal' => $this->subtotal,
                'tax' => $this->tax,
                'shipping_price' => $this->shipping_price ?? 0,
                'total' => $this->total,
                'status' => 'en_proceso',
                'source_version_id' => $this->source_version_id,
            ]);

            // 5. Guardar snapshots de ExchangeRates para esta versión
            $this->saveExchangeRateSnapshots($quoteVersion);

            // 6. Guardar los items de la cotización
            $this->saveQuoteItems($quoteVersion);

            // 7. Guardar las notas
            $this->saveQuoteNotes($quoteVersion);

            // 8. Guardar los conceptos adicionales
            $this->saveQuoteConcepts($quoteVersion);

            // 9. Limpiar sesión
            session()->forget('quote_items');

            return $quoteVersion;
        });
    }

    /**
     * Guardar snapshots de los exchange rates vigentes
     */
    private function saveExchangeRateSnapshots(QuoteVersion $quoteVersion): void
    {
        // Obtener todos los exchange rates actuales
        $currentRates = $this->currents;

        foreach ($currentRates as $rate) {
            QuoteExchangeRate::create([
                'quote_version_id' => $quoteVersion->id,
                'from_currency' => $rate['base_currency'],
                'to_currency' => $rate['target_currency'],
                'rate' => $rate['rate'],
            ]);
        }
    }

    /**
     * Guardar los items de la cotización
     */
    private function saveQuoteItems(QuoteVersion $quoteVersion): void
    {
        foreach ($this->items as $item) {

            QuoteItem::create([
                'quote_version_id' => $quoteVersion->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price_original' => $item['unit_price_original'],
                'currency_original' => $item['currency_original'],
                'unit_price_converted' => $item['unit_price_converted'],
                'exchange_rate_used' => $item['exchange_rate_used'],
                'custom_price' => $item['custom_price'] ?? false,
                'unit_price_converted_custom' => $item['unit_price_converted_custom'],
                'discount' => $item['discount'] ?? 0,
            ]);
        }
    }

    /**
     * Guardar las notas de la cotización
     */
    private function saveQuoteNotes(QuoteVersion $quoteVersion): void
    {
        foreach ($this->notes as $note) {
            if (! empty($note['note'])) {
                QuoteNote::create([
                    'quote_version_id' => $quoteVersion->id,
                    'note' => $note['note'],
                ]);
            }
        }
    }

    /**
     * Guardar los conceptos adicionales
     */
    private function saveQuoteConcepts(QuoteVersion $quoteVersion): void
    {
        foreach ($this->concepts as $concept) {
            QuoteConcept::create([
                'quote_version_id' => $quoteVersion->id,
                'name' => $concept['name'],
                'description' => $concept['description'] ?? null,
                'unit_price' => $concept['unit_price'],
                'quantity' => $concept['quantity'] ?? 1,
                'discount' => $concept['discount'] ?? 0,
            ]);
        }
    }

    public function addConcept(array $data): void
    {
        if (empty($data['name']) || (! isset($data['unit_price']) || $data['unit_price'] === '' || $data['unit_price'] === null)) {
            return;
        }

        $this->concepts[] = [
            'name' => (string) $data['name'],
            'description' => (string) ($data['description'] ?? ''),
            'unit_price' => (float) $data['unit_price'],
            'quantity' => (float) ($data['quantity'] ?? 1),
            'discount' => (float) ($data['discount'] ?? 0),
        ];
    }

    public function removeConcept(int $index): void
    {
        unset($this->concepts[$index]);
        $this->concepts = array_values($this->concepts);
    }

    public function updateConcept(int $index, string $field, $value): void
    {
        if (isset($this->concepts[$index])) {
            $this->concepts[$index][$field] = $value;
        }
    }

    /**
     * Convertir precio a USD usando tasas de cambio.
     * Tasa almacenada como USD→TARGET, por tanto para convertir TARGET→USD se divide: amount / rate.
     * Ejemplo: 1000 MXN / 17.35 (USD→MXN) = 57.64 USD
     */
    private function convertToUSD($amount, $fromCurrency)
    {
        if ($fromCurrency === 'USD') {
            return $amount;
        }

        // Buscar tasa de cambio de USD a la moneda origen
        $rate = collect($this->currents)->firstWhere('target_currency', $fromCurrency);

        if (! $rate) {
            \Log::warning("QuoteForm::convertToUSD — no se encontró tasa para {$fromCurrency}, retornando amount sin convertir", [
                'currents' => $this->currents,
            ]); // TODO: remove

            return $amount;
        }

        // Convertir: amount / rate = USD
        return $amount / floatval($rate['rate']);
    }

    /**
     * Convertir USD a cualquier moneda
     */
    private function convertUsdToCurrency(float $usdAmount, string $targetCurrency): float
    {
        if ($targetCurrency === 'USD') {
            return $usdAmount;
        }

        $rate = $this->getExchangeRate($targetCurrency);

        return $usdAmount * $rate;
    }

    private function convertUsdToCurrencySnapshot(float $usdAmount, string $targetCurrency): float
    {
        if ($targetCurrency === 'USD') {
            return $usdAmount;
        }

        $rate = $this->getExchangeRateFromSnapshot($targetCurrency);

        return $usdAmount * $rate;
    }

    /**
     * Obtener tasa de cambio para una moneda específica.
     * Devuelve la tasa USD→currency (ej: USD→MXN = 17.35).
     * Para reconstruir precio original: price_original = unit_price_converted * exchange_rate_used
     * Para convertir a USD: unit_price_converted = price_original / exchange_rate_used
     */
    public function getExchangeRate($currency)
    {
        if ($currency === 'USD') {
            return 1;
        }

        $rate = collect($this->currents)->firstWhere('target_currency', $currency);

        if (! $rate) {
            \Log::warning("QuoteForm::getExchangeRate — no se encontró tasa para {$currency}, usando fallback 1", [
                'currents' => $this->currents,
            ]); // TODO: remove

            return 1;
        }

        return floatval($rate['rate']);
    }

    public function updateExchangeRate($currency, $mount)
    {
        // buscamos el currency en currents
        $index = collect($this->currents)->search(fn ($c) => $c['target_currency'] === $currency);
        if ($index !== false) {
            $this->currents[$index]['rate'] = $mount;
        }
    }

    private function getExchangeRateFromSnapshot($currency)
    {
        if ($currency === 'USD') {
            return 1;
        }

        $rate = collect($this->exchangeRatesSnapshot)->firstWhere('to_currency', $currency);

        return $rate ? floatval($rate['rate']) : 1;
    }

    /**
     * Agregar o quitar producto seleccionado
     */
    public function toggleProduct($productId)
    {
        $product = Product::find($productId);

        if (! $product) {
            return;
        }

        // Verificar si ya está en items
        $existingIndex = collect($this->items)->search(fn ($item) => $item['product_id'] == $productId);

        if ($existingIndex !== false) {
            // Quitar producto
            unset($this->items[$existingIndex]);
            $this->items = array_values($this->items);
        } else {
            // Agregar producto
            $convertedPrice = $this->convertToUSD($product->price_regular, $product->currency);
            $exchangeRate = $this->getExchangeRate($product->currency);

            // exchange_rate_used = tasa USD→moneda_original (ej: 17.35 para MXN)
            // Para reconstruir: price_original = unit_price_converted * exchange_rate_used
            $newItem = [
                'quote_id' => $this->quote_id ?? null,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'product_image_url' => $product->image_url,
                'quantity' => 1,
                'unit_price_original' => $product->price_regular,
                'currency_original' => $product->currency,
                'unit_price_converted' => $convertedPrice,
                'exchange_rate_used' => $exchangeRate,
                'custom_price' => false,
                'unit_price_converted_custom' => $convertedPrice,
                'discount' => 0,
            ];

            \Log::info('QuoteForm::toggleProduct', [
                'product_id' => $product->id,
                'product_currency' => $product->currency,
                'price_regular' => $product->price_regular,
                'exchange_rate_used' => $exchangeRate,
                'unit_price_converted' => $convertedPrice,
                'unit_price_converted_custom' => $convertedPrice,
            ]); // TODO: remove

            $this->items[] = $newItem;
        }

        // Guardar en sesión o persistir
        session()->put('quote_items', $this->items);
    }

    /**
     * Actualizar cantidad de un item
     */
    public function updateQuantity($index, $quantity)
    {
        if (isset($this->items[$index]) && $quantity > 0) {
            $this->items[$index]['quantity'] = $quantity;
        }
    }

    /**
     * Actualizar precio personalizado
     */
    public function updateCustomPrice($index, $price)
    {
        // hay que corregir esto, pues el unit price original y el convertido se quedan igual
        // pero hay que añadir un campo mas por si se modifica el precio y ese sera el precio cambiado
        // en moneda base
        if (isset($this->items[$index])) {
            $this->items[$index]['unit_price_converted_custom'] = floatval($price);
            $this->items[$index]['custom_price'] = true;
        }
    }

    /**
     * Actualizar descuento
     */
    public function updateDiscount($index, $discount)
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['discount'] = floatval($discount);
        }
    }

    /**
     * Resetear precio original
     */
    public function resetPrice($index)
    {
        if (isset($this->items[$index])) {
            $product = Product::find($this->items[$index]['product_id']);
            if ($product) {
                $convertedPrice = $this->convertToUSD($product->price_regular, $product->currency);
                $this->items[$index]['unit_price_converted_custom'] = $convertedPrice;
                $this->items[$index]['custom_price'] = false;
            }
        }
    }

    /**
     * Eliminar item del listado
     */
    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        session()->put('quote_items', $this->items);
    }

    /**
     * Calcular subtotal de un item
     */
    public function itemSubtotal($item)
    {
        if ($item['discount'] > 0) {
            return $item['unit_price_converted_custom'] * $item['quantity'] * (1 - $item['discount'] / 100);
        }

        return $item['quantity'] * $item['unit_price_converted_custom'];
    }

    /**
     * Calcular subtotal de un item con descuento
     */
    public function itemSubtotalNoDiscount($item): float|int
    {
        return $item['quantity'] * $item['unit_price_converted_custom'];
    }

    private function conceptSubtotalNoDiscount(array $concept): float
    {
        return (float) $concept['unit_price'] * (float) $concept['quantity'];
    }

    private function conceptSubtotal(array $concept): float
    {
        return $this->conceptSubtotalNoDiscount($concept) * (1 - (float) $concept['discount'] / 100);
    }

    public function itemsTotalNoDiscount()
    {
        $itemsTotal = collect($this->items)->sum(fn ($item) => $this->itemSubtotalNoDiscount($item));
        $conceptsTotal = collect($this->concepts)->sum(fn ($concept) => $this->conceptSubtotalNoDiscount($concept));

        return $itemsTotal + $conceptsTotal;
    }

    public function itemsTotalDiscount()
    {
        $itemsTotal = collect($this->items)->sum(fn ($item) => $this->itemSubtotal($item));
        $conceptsTotal = collect($this->concepts)->sum(fn ($concept) => $this->conceptSubtotal($concept));

        return $itemsTotal + $conceptsTotal;
    }

    public function getSubtotalProperty()
    {
        $itemsTotal = collect($this->items)->sum(fn ($item) => $this->itemSubtotal($item));
        $conceptsTotal = collect($this->concepts)->sum(fn ($concept) => $this->conceptSubtotal($concept));
        $shipping = is_numeric($this->shipping_price) ? floatval($this->shipping_price) : 0;
        $this->subtotal = $itemsTotal + $conceptsTotal + $shipping;

        return $this->subtotal;
    }

    public function getTaxProperty()
    {
        $this->tax = round($this->subtotal * QuoteVersion::TAX_RATE, 4);

        return $this->tax;
    }

    public function getTotalProperty()
    {
        $this->total = $this->subtotal + $this->tax;

        return $this->total;
    }

    /**
     * Calcular todos los totals para una moneda específica
     */
    private function calculateTotalsForCurrency(string $currency): array
    {
        // Calcular en USD primero
        $itemsTotalNoDiscountUsd = $this->itemsTotalNoDiscount();
        $itemsTotalDiscountUsd = $this->itemsTotalDiscount();
        $subtotalUsd = $this->subtotal;
        $taxUsd = $this->tax;
        $shippingUsd = is_numeric($this->shipping_price) ? floatval($this->shipping_price) : 0;
        $totalUsd = $this->total;

        if ($currency === 'USD') {
            return [
                'currency' => 'USD',
                'rate' => 1,
                'items_total_no_discount' => $itemsTotalNoDiscountUsd,
                'items_total_discount' => $itemsTotalDiscountUsd,
                'subtotal' => $subtotalUsd,
                'tax' => $taxUsd,
                'shipping' => $shippingUsd,
                'total' => $totalUsd,
            ];
        }

        // Convertir a la moneda destino
        $rate = $this->getExchangeRate($currency);

        return [
            'currency' => $currency,
            'rate' => $rate,
            'items_total_no_discount' => $this->convertUsdToCurrency($itemsTotalNoDiscountUsd, $currency),
            'items_total_discount' => $this->convertUsdToCurrency($itemsTotalDiscountUsd, $currency),
            'subtotal' => $this->convertUsdToCurrency($subtotalUsd, $currency),
            'tax' => $this->convertUsdToCurrency($taxUsd, $currency),
            'shipping' => $this->convertUsdToCurrency($shippingUsd, $currency),
            'total' => $this->convertUsdToCurrency($totalUsd, $currency),
        ];
    }

    /**
     * Obtener todos los cálculos para todas las monedas disponibles
     */
    public function getAllCurrenciesCalculations(): array
    {
        $calculations = [];

        // Calcular para cada moneda disponible
        foreach ($this->currents as $currency) {
            $c = $currency['target_currency'];
            $calculations[$c] = $this->calculateTotalsForCurrency($c);
        }

        return $calculations;
    }

    /**
     * Propiedad computada para todos los cálculos (accesible desde Blade)
     */
    public function getCurrencyCalculationsProperty(): array
    {
        return $this->getAllCurrenciesCalculations();
    }

    public function addNote($content)
    {
        if (empty($content)) {
            return;
        }
        $newNote = [
            'note' => $content,
        ];

        $this->notes[] = $newNote;

    }

    public function removeNote($index)
    {
        unset($this->notes[$index]);
        $this->notes = array_values($this->notes); // Reindexar array
    }

    /**
     * Cargar una cotización existente para mostrar
     */
    public function loadQuote(QuoteVersion $quoteVersion): void
    {
        // Datos básicos
        $this->quote_id = $quoteVersion->quote_id;
        $this->version_letter = $quoteVersion->version_letter;
        $this->folio = $quoteVersion->folio;
        $this->company_id = $quoteVersion->quote->company_id;
        $this->base_currency = $quoteVersion->quote->base_currency ?? 'USD';
        $this->subtotal = $quoteVersion->subtotal;
        $this->tax = $quoteVersion->tax;
        $this->shipping_price = $quoteVersion->shipping_price;
        $this->total = $quoteVersion->total;
        $this->status = $quoteVersion->status;
        $this->source_version_id = $quoteVersion->source_version_id;

        // Timestamps
        $this->timestamps = [
            'created_at' => $quoteVersion->created_at,
            'created_by' => $quoteVersion->quote->user?->name ?? 'N/A',
            'updated_at' => $quoteVersion->updated_at,
        ];

        // Cargar items formateados
        $this->loadFormattedItems($quoteVersion);

        // Cargar snapshots de exchange rates
        $this->loadExchangeRatesSnapshot($quoteVersion);

        // Cargar notas
        $this->loadNotes($quoteVersion);
    }

    /**
     * Cargar y formatear items con todos sus cálculos
     */
    private function loadFormattedItems(QuoteVersion $quoteVersion): void
    {
        $this->items = [];
        $this->concepts = [];

        foreach ($quoteVersion->items as $item) {
            // Reconstruir el mismo formato que usas en create
            $this->items[] = [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name ?? 'Producto eliminado',
                'product_sku' => $item->product->sku ?? 'N/A',
                'product_image_url' => $item->product->image_url ?? null,
                'quantity' => $item->quantity,
                'unit_price_original' => $item->unit_price_original,
                'currency_original' => $item->currency_original,
                'unit_price_converted' => $item->unit_price_converted,
                'exchange_rate_used' => $item->exchange_rate_used,
                'custom_price' => $item->custom_price,
                'unit_price_converted_custom' => $item->unit_price_converted_custom,
                'discount' => $item->discount,
                'subtotal' => ($item->unit_price_converted_custom * $item->quantity),
                'total' => ($item->unit_price_converted_custom * $item->quantity) * (1 - $item->discount / 100),
                // Cálculos adicionales para mostrar
                'subtotal_without_discount' => $item->quantity * $item->unit_price_converted_custom,
                'discount_amount' => ($item->quantity * $item->unit_price_converted_custom) * ($item->discount / 100),
            ];
        }

        foreach ($quoteVersion->concepts as $c) {
            $this->concepts[] = [
                'name' => $c->name,
                'description' => $c->description,
                'unit_price' => (float) $c->unit_price,
                'quantity' => (float) $c->quantity,
                'discount' => (float) $c->discount,
            ];
        }
    }

    /**
     * Cargar snapshots de exchange rates usados en esta versión
     */
    private function loadExchangeRatesSnapshot(QuoteVersion $quoteVersion): void
    {
        $this->exchangeRatesSnapshot = [];

        foreach ($quoteVersion->exchangeRates as $rate) {
            $this->exchangeRatesSnapshot[] = [
                'from_currency' => $rate->from_currency,
                'to_currency' => $rate->to_currency,
                'rate' => $rate->rate,
                'created_at' => $rate->created_at,
            ];
        }
    }

    /**
     * Cargar notas de la versión
     */
    private function loadNotes(QuoteVersion $quoteVersion): void
    {
        $this->notes = [];

        foreach ($quoteVersion->notes as $note) {
            $this->notes[] = [
                'note' => $note->note,
                'created_at' => $note->created_at,
            ];
        }
    }

    /**
     * Obtener información de la versión anterior (si existe)
     */
    public function getPreviousVersionInfo(): ?array
    {
        if (! $this->source_version_id) {
            return null;
        }

        $previousVersion = QuoteVersion::find($this->source_version_id);

        if (! $previousVersion) {
            return null;
        }

        return [
            'id' => $previousVersion->id,
            'folio' => $previousVersion->folio,
            'version_letter' => $previousVersion->version_letter,
            'created_at' => $previousVersion->created_at,
            'created_by' => $previousVersion->quote->user?->name ?? 'N/A',
        ];
    }

    /**
     * Verificar si esta es una recotización
     */
    public function isRecotization(): bool
    {
        return $this->source_version_id > 0;
    }

    /**
     * Pre-llena el form para una REVISIÓN:
     * copia items + snapshot histórico de tasas de la versión origen.
     */
    public function prefillFromRevision(QuoteVersion $sourceVersion): void
    {
        $this->source_version_id = $sourceVersion->id;
        $this->source_version_letter = $sourceVersion->version_letter;
        $this->shipping_price = (float) $sourceVersion->shipping_price;

        // Tasas históricas en formato currents (solo pares USD → otra moneda)
        $this->currents = $sourceVersion->exchangeRates
            ->filter(fn ($r) => $r->from_currency === 'USD')
            ->map(fn ($r) => [
                'base_currency' => $r->from_currency,
                'target_currency' => $r->to_currency,
                'rate' => (float) $r->rate,
            ])
            ->values()
            ->toArray();

        // Items con precios históricos intactos
        $this->items = $sourceVersion->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'product_name' => $item->product->name ?? 'Producto eliminado',
            'product_sku' => $item->product->sku ?? 'N/A',
            'product_image_url' => $item->product->image_url ?? null,
            'quantity' => (float) $item->quantity,
            'unit_price_original' => (float) $item->unit_price_original,
            'currency_original' => $item->currency_original,
            'unit_price_converted' => (float) $item->unit_price_converted,
            'exchange_rate_used' => (float) $item->exchange_rate_used,
            'custom_price' => (bool) $item->custom_price,
            'unit_price_converted_custom' => (float) $item->unit_price_converted_custom,
            'discount' => (float) $item->discount,
        ])->toArray();

        // Notas copiadas (sin created_at, se registrarán al guardar)
        $this->notes = $sourceVersion->notes->map(fn ($note) => [
            'note' => $note->note,
        ])->toArray();

        // Conceptos copiados
        $this->concepts = $sourceVersion->concepts->map(fn ($c) => [
            'name' => $c->name,
            'description' => $c->description,
            'unit_price' => (float) $c->unit_price,
            'quantity' => (float) $c->quantity,
            'discount' => (float) $c->discount,
        ])->toArray();
    }

    /**
     * Pre-llena el form para una RECOTIZACIÓN:
     * copia items recalculando precios con tasas frescas ya cargadas en $this->currents.
     * No copia tasas históricas ni source_version_id.
     */
    public function prefillFromRecotizacion(QuoteVersion $sourceVersion): void
    {
        $this->shipping_price = (float) $sourceVersion->shipping_price;

        // Items con precios originales, conversiones recalculadas con tasas frescas
        $this->items = $sourceVersion->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'product_name' => $item->product->name ?? 'Producto eliminado',
            'product_sku' => $item->product->sku ?? 'N/A',
            'product_image_url' => $item->product->image_url ?? null,
            'quantity' => (float) $item->quantity,
            'unit_price_original' => (float) $item->unit_price_original,
            'currency_original' => $item->currency_original,
            'unit_price_converted' => $this->convertToUSD($item->unit_price_original, $item->currency_original),
            'exchange_rate_used' => $this->getExchangeRate($item->currency_original),
            'custom_price' => (bool) $item->custom_price,
            'unit_price_converted_custom' => $item->custom_price
                ? (float) $item->unit_price_converted_custom
                : $this->convertToUSD($item->unit_price_original, $item->currency_original),
            'discount' => (float) $item->discount,
        ])->toArray();

        // Notas copiadas
        $this->notes = $sourceVersion->notes->map(fn ($note) => [
            'note' => $note->note,
        ])->toArray();

        // Conceptos copiados
        $this->concepts = $sourceVersion->concepts->map(fn ($c) => [
            'name' => $c->name,
            'description' => $c->description,
            'unit_price' => (float) $c->unit_price,
            'quantity' => (float) $c->quantity,
            'discount' => (float) $c->discount,
        ])->toArray();
    }

    /**
     * Calcular resumen de conversiones por moneda
     */
    public function getCurrencySummary(): array
    {
        $summary = [];

        // Primero, inicializar todas las monedas disponibles en exchangeRatesSnapshot
        foreach ($this->exchangeRatesSnapshot as $rate) {
            $currency = $rate['to_currency'];

            if (! isset($summary[$currency])) {
                $summary[$currency] = [
                    'currency' => $currency,
                    'exchange_rate' => $rate['rate'],
                    'subtotal_products' => $this->convertUsdToCurrencySnapshot($this->subtotal - ($this->shipping_price ?? 0), $currency),
                    'shipping_price' => $this->convertUsdToCurrencySnapshot($this->shipping_price ?? 0, $currency),
                    'subtotal' => $this->convertUsdToCurrencySnapshot($this->subtotal, $currency),
                    'tax' => $this->convertUsdToCurrencySnapshot($this->tax, $currency),
                    'total' => $this->convertUsdToCurrencySnapshot($this->total, $currency),
                ];
            }
        }

        return array_values($summary);
    }
}
