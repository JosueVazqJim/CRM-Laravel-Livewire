@props(['lead'])

<x-mary-card class="shadow-xl bg-gradient-to-br from-base-100 to-base-200">
    <x-slot:title>
        <div class="flex items-center gap-2">
            <x-mary-icon name="o-information-circle" class="h-5 w-5"></x-mary-icon>
            <span>Información General</span>
        </div>
    </x-slot:title>

    <div class="space-y-4">
        {{-- Nombre de Empresa --}}
        <div>
            <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Empresa Cliente</label>
            <div class="text-base font-medium">{{ $lead->name_company ?? 'Sin empresa' }}</div>
        </div>

        {{-- Empresa Corporativa --}}
        @if($lead->company)
            <div>
                <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Empresa Corporativa</label>
                <div class="flex items-center gap-2">
                    <x-mary-avatar
                        :image="$lead->company->avatar_url"
                        class="!w-8 !h-8"
                    />
                    <span class="font-medium">{{ $lead->company->name }}</span>
                </div>
            </div>
        @endif

        {{-- Usuario Creador --}}
        @if($lead->user)
            <div>
                <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Creado por</label>
                <div class="flex items-center gap-2">
                    <x-mary-avatar
                        :image="$lead->user->avatar_url"
                        class="!w-8 !h-8"
                    />
                    <span class="font-medium">
                        {{ $lead->user->first_name }}
                        {{ $lead->user->paternal_surname }}
                        {{ $lead->user->maternal_surname }}
                    </span>
                </div>
            </div>
        @endif

        {{-- Estado --}}
        <div>
            <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Estado</label>
            <div class="font-medium">{{ $lead->state->name ?? 'No disponible' }}</div>
        </div>

        {{-- Fecha de Creación --}}
        <div>
            <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Creado el</label>
            <div class="font-medium">
                {{ $lead->creat_at?->format('d/m/Y H:i') ?? 'No disponible' }}
            </div>
        </div>
    </div>
</x-mary-card>

