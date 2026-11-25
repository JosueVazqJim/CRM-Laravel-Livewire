@props(['user', 'roles' => [],])

<x-mary-card class="shadow-xl bg-gradient-to-br from-base-100 to-base-200">
    <div class="flex flex-col items-center text-center">
        {{-- Foto de perfil --}}
        <div class="relative mb-4">
            <div class="h-40 w-40 rounded-full overflow-hidden border-4 border-primary/20 shadow-lg">
                <img src="{{ $user->avatar_url }}" class="h-full w-full object-cover" alt="{{ $user->first_name }}" />
            </div>

            {{-- Badge overlay: activo / desactivado --}}
            <div class="absolute -bottom-2 right-0">
                <x-mary-badge
                        :value="$user->active ? 'Activo' : 'Desactivado'"
                        class="{{ $user->active ? 'badge badge-sm badge-primary' : 'badge badge-sm badge-error' }}"
                />
            </div>
        </div>

        {{-- Nombre completo --}}
        <h2 class="text-2xl font-bold">
            {{ $user->first_name }} {{ $user->paternal_surname }} {{ $user->maternal_surname }}
        </h2>

        {{-- Email y RFC --}}
        <p class="flex items-center gap-1 mt-1 text-sm">
            <x-mary-icon name="o-envelope" class="w-4 h-4" />
            {{ $user->email }}
        </p>

        @if($user->rfc)
            <p class="text-sm text-gray-800 dark:text-gray-400 mt-2 font-mono">
                RFC: {{ $user->rfc }}
            </p>
        @endif

{{--        roles--}}
        @if(count($roles) > 0)
            <div class="flex items-center gap-2 mt-3">
                @foreach($roles as $role)
                    <x-mary-badge :value="$role" class="badge-sm badge-primary" />
                @endforeach
            </div>
        @endif

        {{-- Metadatos --}}
        <div class="divider"></div>

        <div class="w-full space-y-2 text-left">

            <div class="flex justify-between py-2 border-b">
                <span class="font-semibold text-gray-800 dark:text-gray-400">Sexo:</span>
                <span>{{ $user->sex ?? 'N/A' }}</span>
            </div>

            <div class="flex justify-between py-2 border-b">
                <span class="font-semibold text-gray-800 dark:text-gray-400">Usuario desde:</span>
                <span>{{ $user->created_at?->format('d/m/Y') }}</span>
            </div>

            @if($user->updated_at && $user->updated_at != $user->created_at)
                <div class="flex justify-between py-2 border-b">
                    <span class="font-semibold text-gray-800 dark:text-gray-400">Última actualización:</span>
                    <span>{{ $user->updated_at?->format('d/m/Y') }}</span>
                </div>
            @endif
        </div>
    </div>
</x-mary-card>