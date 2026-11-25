<?php

namespace App\Observers;

use App\Models\Products\ProductSerial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductSerialObserver
{
    /**
     * Recalcula stock_count en el producto padre cada vez que
     * un serial es creado, actualizado o eliminado.
     */
    public function created(ProductSerial $serial): void
    {
        Log::info("🔵 ProductSerialObserver::created() - Serial ID: {$serial->id}, Product ID: {$serial->product_id}");
        $this->syncStock($serial);
    }

    public function updated(ProductSerial $serial): void
    {
        // Solo recalcular si cambió el campo status
        if ($serial->wasChanged('status')) {
            Log::info("🟡 ProductSerialObserver::updated() - Serial ID: {$serial->id}, Status changed from {$serial->getOriginal('status')} to {$serial->status}");
            $this->syncStock($serial);
        }
    }

    public function deleted(ProductSerial $serial): void
    {
        Log::info("🔴 ProductSerialObserver::deleted() - Serial ID: {$serial->id}, Product ID: {$serial->product_id}");
        $this->syncStock($serial);
    }

    // -------------------------------------------------------------------------

    private function syncStock(ProductSerial $serial): void
    {
        try {
            // Obtener el product_id directamente
            $productId = $serial->product_id;
            //            Log::info("🔍 Intentando sincronizar stock para producto {$productId}");

            // Contar seriales en stock usando query directa
            $newCount = DB::table('product_serials')
                ->where('product_id', $productId)
                ->where('status', ProductSerial::STATUS_IN_STOCK)
                ->count();

            //            Log::info("📊 New Stock Count: {$newCount}");

            // Actualizar usando query directa para evitar problemas con el modelo
            $updated = DB::table('products')
                ->where('id', $productId)
                ->update(['stock_count' => $newCount]);

            //            Log::info("📝 DB::update() retornó: {$updated}");
            //
            //            if ($updated) {
            //                Log::info("✅ Stock actualizado a {$newCount} para producto {$productId}");
            //            } else {
            //                Log::error("❌ DB::update() no actualizó ninguna fila");
            //            }
        } catch (\Throwable $e) {
            Log::error('❌ ProductSerialObserver::syncStock() Error: '.$e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
}
