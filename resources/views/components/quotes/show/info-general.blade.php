@props([
    'lead',
    'company',
    'quoteForm',
    'statusBadge'
])

<div class="card bg-base-100 shadow-sm">
    <div class="card-body">
        <h2 class="card-title">Información General</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Lead/Cliente</p>
                <p class="font-medium">{{ $lead->name_company ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Empresa Emisora</p>
                <p class="font-medium">{{ $company->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Creado por</p>
                <p class="font-medium">{{ $quoteForm->timestamps['created_by'] }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Moneda Base</p>
                <p class="font-medium">{{ $quoteForm->base_currency }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Última actualización</p>
                <p class="font-medium">{{ $quoteForm->timestamps['updated_at']->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Estado</p>
                <p class="font-medium">{{ $statusBadge['label'] }}</p>
            </div>
        </div>
    </div>
</div>