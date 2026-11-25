@props([
    'quoteVersion',
    'purchaseOrder',
    'totalMxn',
])

@if($quoteVersion->status === 'exitosa')
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <h2 class="card-title">Información de Orden de Compra</h2>
                <x-mary-button
                    wire:click="openPurchaseOrderModal"
                    class="btn-sm btn-outline"
                    icon="{{ $purchaseOrder ? 'o-pencil-square' : 'o-plus' }}"
                    label="{{ $purchaseOrder ? 'Editar' : 'Registrar' }}"
                />
            </div>

            @if($purchaseOrder)
                @php
                    $restan = $totalMxn !== null ? $totalMxn - (float) $purchaseOrder->advance_amount : null;
                @endphp
                <div class="grid grid-cols-2 gap-4 mt-2">
                    <div>
                        <p class="text-sm text-gray-500">Fecha de anticipo</p>
                        <p class="font-medium">{{ $purchaseOrder->advance_paid_at?->format('d/m/Y') ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Monto</p>
                        <p class="font-medium">
                            {{ $purchaseOrder->advance_amount !== null ? number_format($purchaseOrder->advance_amount, 2) . ' MXN' : 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Medio de pago</p>
                        <p class="font-medium">{{ $purchaseOrder->payment_method ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Referencia</p>
                        <p class="font-medium">{{ $purchaseOrder->reference ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Restan</p>
                        <p class="font-medium">
                            {{ $restan !== null ? number_format($restan, 2) . ' MXN' : 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Fecha de liquidación</p>
                        <p class="font-medium">{{ $purchaseOrder->settlement_date?->format('d/m/Y') ?? 'N/A' }}</p>
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-500 mt-2">Aún no se ha registrado información de pago para esta orden de compra.</p>
            @endif
        </div>
    </div>
@endif
