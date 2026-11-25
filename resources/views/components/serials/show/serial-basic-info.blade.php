@props(['serial'])

<x-mary-card class="shadow-lg bg-linear-to-br from-base-100 to-base-200">
    <x-slot:title class="flex items-center gap-2">
        <x-mary-icon name="o-identification" />
        Serial #{{ $serial->serial_number }}
    </x-slot:title>

    <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-base-content/60 mb-1">Número de Serie</p>
                <p class="text-lg font-semibold">{{ $serial->serial_number }}</p>
            </div>
            <div>
                <p class="text-sm text-base-content/60 mb-1">ID</p>
                <p class="text-lg font-semibold">#{{ $serial->id }}</p>
            </div>
        </div>

        <div class="divider my-2"></div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-base-content/60 mb-1">Registrado por</p>
                <p class="font-semibold">{{ $serial->registeredBy?->name ?? 'Sin asignar' }}</p>
                <p class="text-xs text-base-content/50">{{ $serial->registeredBy?->email ?? '' }}</p>
            </div>
            <div>
                <p class="text-sm text-base-content/60 mb-1">Creado el</p>
                <p class="font-semibold">{{ $serial->created_at->format('d/m/Y') }}</p>
                <p class="text-xs text-base-content/50">{{ $serial->created_at->format('H:i') }}</p>
            </div>
            <div>
                <p class="text-sm text-base-content/60 mb-1">Actualizado el</p>
                <p class="font-semibold">{{ $serial->updated_at->format('d/m/Y') }}</p>
                <p class="text-xs text-base-content/50">{{ $serial->updated_at->format('H:i') }}</p>
            </div>
        </div>
    </div>
</x-mary-card>

