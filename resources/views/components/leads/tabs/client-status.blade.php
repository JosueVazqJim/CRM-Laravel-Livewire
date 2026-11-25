@props(['client_progress' => '', 'client_status' => '', 'pay_status' => '', 'after_sales' => '', 'tabErrors' => []])

<x-slot:label>
    <div class="flex items-center gap-1 whitespace-nowrap">
        @if($tabErrors['client-status'] ?? false)
            <x-mary-badge value="{{ $tabErrors['client-status'] }}" class="badge-error badge-sm"/>
        @endif
    </div>
</x-slot:label>

<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Fase de Ventas (Client Progress) --}}
        <div>
            <x-mary-select
                label="Fase de Ventas"
                icon="o-chart-bar"
                wire:model="client_progress"
                :options="[
                    ['id' => 'nuevo', 'name' => 'Nuevo'],
                    ['id' => 'sin contactar', 'name' => 'Sin contactar'],
                    ['id' => 'negociación', 'name' => 'Negociación'],
                    ['id' => 'diagnosticado', 'name' => 'Diagnosticado'],
                    ['id' => 'contactado', 'name' => 'Contactado'],
                    ['id' => 'cierre(pendiente adelanto)', 'name' => 'Cierre(pendiente adelanto)'],
                    ['id' => 'vendido', 'name' => 'Vendido'],
                    ['id' => 'perdido', 'name' => 'Perdido'],
                ]"
                :error="$errors->first('client_progress')"
                class="input-primary"
            />
        </div>

        {{-- Estado del Cliente --}}
        <div>
            <x-mary-select
                label="Estado del Cliente"
                icon="o-user-group"
                wire:model="client_status"
                :options="[
                            ['id' => 'en pausa', 'name' => 'En pausa'],
                            ['id' => 'sin respuesta', 'name' => 'Sin respuesta'],
                            ['id' => 'en proceso', 'name' => 'En proceso'],
                        ]"
                :error="$errors->first('client_status')"
                class="input-primary"
            />
        </div>

        {{-- Estado de Pago --}}
        <div>
            <x-mary-select
                label="Estado de Pago"
                icon="o-credit-card"
                wire:model="pay_status"
                :options="[
                    ['id' => 'sin cierre aun', 'name' => 'Sin cierre aún'],
                    ['id' => 'pago pendiente', 'name' => 'Pago pendiente'],
                    ['id' => 'adelanto', 'name' => 'Adelanto'],
                    ['id' => 'liquidado', 'name' => 'Liquidado'],
                ]"
                :error="$errors->first('pay_status')"
                class="input-primary"
            />
        </div>

        {{-- Servicio Post-Venta --}}
        <div>
            <x-mary-select
                label="Servicio Post-Venta"
                icon="o-star"
                wire:model="after_sales"
                :options="[
                    ['id' => 'sin cierre aun', 'name' => 'Sin cierre aún'],
                    ['id' => 'espera de producto', 'name' => 'Espera de producto'],
                    ['id' => 'pedido preparado', 'name' => 'Pedido preparado'],
                    ['id' => 'pedido enviado', 'name' => 'Pedido enviado'],
                    ['id' => 'pedido entregado', 'name' => 'Pedido entregado'],
                ]"
                :error="$errors->first('after_sales')"
                class="input-primary"
            />
        </div>
    </div>

    {{-- Indicador de errores en la pestaña --}}
    @if($tabErrors['client_status'] ?? 0)
        <div class="alert alert-warning">
            <x-mary-icon name="o-exclamation-triangle" class="w-4 h-4" />
            <span>Hay {{ $tabErrors['client_status'] }} campo(s) con error en esta sección.</span>
        </div>
    @endif
</div>

