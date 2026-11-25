<x-mary-card class="shadow-xl">
    <x-slot:title>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-mary-icon name="o-user-group" class="h-5 w-5" />
                <span>Estado del Cliente</span>
            </div>

            @if(!$editing)
                <x-mary-button
                        icon="o-pencil"
                        label="Editar"
                        wire:click="enableEditing"
                        class="btn-sm"
                />
            @endif
        </div>
    </x-slot:title>

    <div class="space-y-4">
        @if($editing)
            {{-- Fase de Ventas --}}
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
                        class="input-primary"
                />
            </div>

            {{-- Post-Venta --}}
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
                        class="input-primary"
                />
            </div>
            <div class="flex gap-2 justify-end">
                <x-mary-button
                        label="Cancelar"
                        wire:click="cancelEditing"
                        wire:loading.attr="disabled"
                        wire:target="cancelEditing"
                        class="btn-ghost"
                />

                <x-mary-button
                        label="Guardar"
                        wire:click="update"
                        wire:loading.attr="disabled"
                        wire:target="update"
                        class="btn-primary"
                />
            </div>
        @else
            {{-- Fase de Ventas --}}
            <div>
                <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Fase de Ventas</label>
                @php
                    $progressColor = match($lead->client_progress) {
                        'sin contactar' => 'badge-warning',
                        'nuevo' => 'badge-info',
                        'contactado' => 'badge-primary',
                        'diagnosticado' => 'badge-secondary',
                        'negociación' => 'badge-warning',
                        'cierre(pendiente adelanto)' => 'badge-info',
                        'vendido' => 'badge-success',
                        'perdido' => 'badge-error',
                        default => 'badge-secondary'
                    };
                @endphp
                <x-mary-badge :value="ucfirst($lead->client_progress ?? 'No disponible')" class="badge {{ $progressColor }}" />
            </div>

            {{-- Estado del Cliente --}}
            <div>
                <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Estado del Cliente</label>
                <div class="font-medium">{{ $lead->client_status ?? 'No disponible' }}</div>
            </div>

            {{-- Estado de Pago --}}
            <div>
                <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Estado de Pago</label>
                <div class="font-medium">{{ $lead->pay_status ?? 'No disponible' }}</div>
            </div>

            {{-- Post-Venta --}}
            <div>
                <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Post-Venta</label>
                <div class="font-medium">{{ $lead->after_sales ?? 'No disponible' }}</div>
            </div>
        @endif

    </div>
</x-mary-card>

