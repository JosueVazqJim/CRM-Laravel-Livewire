<?php

namespace App\Livewire\Forms\Products;

use AllowDynamicProperties;
use App\Models\Products\ProductSerial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Form;

#[AllowDynamicProperties]
class ProductSerialForm extends Form
{
    public ?ProductSerial $serialModel = null;

    public int $product_id = 0;

    public string $serial_number = '';

    public string $status = 'in_stock';

    public ?string $flag_color = null;

    public string $purchase_price = '';

    public string $purchased_at = '';

    public string $entered_stock_at = '';

    public string $sold_at = '';

    public string $exited_at = '';

    public ?int $sold_by_user_id = null;

    public ?int $quote_version_id = null;

    public ?string $notes = null;

    public bool $isBatchMode = false;

    public array $batchItems = [];

    public function rules(): array
    {
        $shared = [
            'product_id' => ['required', 'exists:products,id'],
            'status' => ['required', 'in:in_stock,sold,damaged,defective'],
            'flag_color' => ['nullable', 'string', 'max:50'],
            'purchase_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'purchased_at' => ['nullable', 'date'],
            'entered_stock_at' => ['nullable', 'date'],
            'sold_at' => ['nullable', 'date'],
            'exited_at' => ['nullable', 'date'],
            'sold_by_user_id' => ['nullable', 'exists:users,id'],
            'quote_version_id' => ['nullable', 'exists:quote_versions,id'],
        ];

        if ($this->isBatchMode) {
            return $shared + [
                'batchItems' => ['required', 'array', 'min:1'],
                'batchItems.*.serial_number' => ['required', 'string', 'max:255', 'distinct:strict', 'unique:product_serials,serial_number'],
                'batchItems.*.notes' => ['nullable', 'string', 'max:1000'],
            ];
        }

        return $shared + [
            'serial_number' => ['required', 'string', 'max:255', 'unique:product_serials,serial_number'.($this->serialModel?->id ? ','.$this->serialModel->id : '')],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'serial_number.required' => 'El número de serie es obligatorio.',
            'serial_number.unique' => 'Este número de serie ya existe en el inventario.',
            'batchItems.required' => 'Debes agregar al menos un serial al lote.',
            'batchItems.min' => 'Debes agregar al menos un serial al lote.',
            'batchItems.*.serial_number.required' => 'El :attribute es obligatorio.',
            'batchItems.*.serial_number.unique' => 'El :attribute ya existe en el inventario.',
            'batchItems.*.serial_number.distinct' => 'El :attribute está repetido dentro de este mismo lote.',
            'batchItems.*.serial_number.max' => 'El :attribute no puede superar los 255 caracteres.',
            'status.required' => 'El estado es obligatorio.',
        ];
    }

    public function validationAttributes(): array
    {
        $attributes = [
            'serial_number' => 'número de serie',
            'status' => 'estado',
            'purchase_price' => 'precio de compra',
            'purchased_at' => 'fecha de compra',
            'entered_stock_at' => 'fecha de entrada a stock',
            'sold_at' => 'fecha de venta',
            'exited_at' => 'fecha de salida',
            'sold_by_user_id' => 'usuario vendedor',
            'quote_version_id' => 'cotización',
        ];

        foreach ($this->batchItems as $index => $item) {
            $row = $index + 1;
            $attributes["batchItems.$index.serial_number"] = "número de serie de la fila #$row";
            $attributes["batchItems.$index.notes"] = "notas de la fila #$row";
        }

        return $attributes;
    }

    public function addBatchRow(): void
    {
        $this->batchItems[] = [
            'key' => (string) Str::uuid(),
            'serial_number' => '',
            'notes' => '',
        ];
    }

    public function removeBatchRow(string $key): void
    {
        if (count($this->batchItems) <= 1) {
            return;
        }

        $this->batchItems = array_values(
            array_filter($this->batchItems, fn ($item) => $item['key'] !== $key)
        );
    }

    public function setSerial(ProductSerial $serial): void
    {
        $this->serialModel = $serial;

        $this->product_id = $serial->product_id;
        $this->serial_number = $serial->serial_number;
        $this->status = $serial->status;
        $this->flag_color = $serial->flag_color;
        $this->purchase_price = (string) $serial->purchase_price;
        $this->purchased_at = $serial->purchased_at?->format('Y-m-d') ?? '';
        $this->entered_stock_at = $serial->entered_stock_at?->format('Y-m-d') ?? '';
        $this->sold_at = $serial->sold_at?->format('Y-m-d\TH:i') ?? '';
        $this->exited_at = $serial->exited_at?->format('Y-m-d') ?? '';
        $this->sold_by_user_id = $serial->sold_by_user_id;
        $this->quote_version_id = $serial->quote_version_id;
        $this->notes = $serial->notes;
    }

    public function store(): ProductSerial
    {
        return ProductSerial::create([
            'product_id' => $this->product_id,
            'user_id' => auth()->id(),
            'serial_number' => $this->serial_number,
            'status' => $this->status,
            'flag_color' => $this->flag_color,
            'purchase_price' => $this->purchase_price ?: null,
            'purchased_at' => $this->purchased_at ?: null,
            'entered_stock_at' => $this->entered_stock_at ?: null,
            'sold_at' => $this->sold_at ?: null,
            'exited_at' => $this->exited_at ?: null,
            'sold_by_user_id' => $this->sold_by_user_id,
            'quote_version_id' => $this->quote_version_id,
            'notes' => $this->notes,
        ]);
    }

    public function storeBatch(): array
    {
        return DB::transaction(function () {
            $created = [];

            foreach ($this->batchItems as $item) {
                $created[] = ProductSerial::create([
                    'product_id' => $this->product_id,
                    'user_id' => auth()->id(),
                    'serial_number' => $item['serial_number'],
                    'status' => $this->status,
                    'flag_color' => $this->flag_color,
                    'purchase_price' => $this->purchase_price ?: null,
                    'purchased_at' => $this->purchased_at ?: null,
                    'entered_stock_at' => $this->entered_stock_at ?: null,
                    'sold_at' => $this->sold_at ?: null,
                    'exited_at' => $this->exited_at ?: null,
                    'sold_by_user_id' => $this->sold_by_user_id,
                    'quote_version_id' => $this->quote_version_id,
                    'notes' => $item['notes'] ?: null,
                ]);
            }

            return $created;
        });
    }

    public function update(): ProductSerial
    {
        $this->serialModel->update([
            'serial_number' => $this->serial_number,
            'status' => $this->status,
            'flag_color' => $this->flag_color,
            'purchase_price' => $this->purchase_price ?: null,
            'purchased_at' => $this->purchased_at ?: null,
            'entered_stock_at' => $this->entered_stock_at ?: null,
            'sold_at' => $this->sold_at ?: null,
            'exited_at' => $this->exited_at ?: null,
            'sold_by_user_id' => $this->sold_by_user_id,
            'quote_version_id' => $this->quote_version_id,
            'notes' => $this->notes,
        ]);

        return $this->serialModel->fresh();
    }
}
