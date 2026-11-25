@props(['company'])

<x-mary-card class="shadow-xl bg-gradient-to-br from-base-100 to-base-200">
    <div class="flex flex-col items-center text-center">
        {{-- Foto de perfil --}}
        <div class="relative mb-4">
            <div class="h-60 w-80 overflow-hidden border-4 border-primary/20 shadow-lg">
                @if($company->avatar)
                    <img src="{{ $company->avatar_url }}" class="h-full w-full object-cover" alt="{{ $company->name }}" />
                @else
                    <img src="{{ asset('images/empty-user.jpg') }}" class="h-full w-full object-cover" />
                @endif
            </div>

            {{-- Badge overlay: activo / desactivado --}}
            <div class="absolute -bottom-2 right-0">
                <x-mary-badge
                        :value="$company->active ? 'Activa' : 'Desactivada'"
                        class="{{ $company->active ? 'badge badge-sm badge-primary' : 'badge badge-sm badge-error' }}"
                />
            </div>
        </div>

        {{-- Nombre --}}
        <h2 class="text-2xl font-bold">
            {{ $company->name }}
        </h2>

        {{-- Slug --}}
        <p class="flex items-center gap-1 mt-1 text-sm">
            <x-mary-icon name="o-chat-bubble-bottom-center-text" class="w-4 h-4" />
            {{ $company->slug }}
        </p>

        {{-- Metadatos --}}
        <div class="divider"></div>

        <div class="w-full space-y-2 text-left">
            <div class="bg-base-300/30 rounded-lg p-3">
                <div class="flex items-start gap-2">
                    <x-mary-icon name="o-document-text" class="w-4 h-4 text-primary/70 mt-0.5" />
                    <div class="flex-1 text-left">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Descripción
                        </span>
                        <p class="text-sm text-gray-700 dark:text-gray-300 mt-0.5">
                            {{ $company->description ?? 'Sin descripción disponible' }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex justify-between">
                <span class= "text-gray-800 dark:text-gray-400" >Creado el:</span>
                <span class="font-medium">{{ $company->created_at?->format('d/m/Y') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-800 dark:text-gray-400">Última actualización:</span>
                <span class="font-medium">{{ $company->updated_at?->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>
</x-mary-card>