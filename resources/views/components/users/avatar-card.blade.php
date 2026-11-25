{{-- Avatar de usuario con slot opcional de acciones a la derecha --}}
@props([
    'user',
])

<div {{ $attributes->class(['flex w-full min-w-0 items-center gap-3 px-2']) }}>
    {{-- Imagen --}}
    <div class="avatar shrink-0">
        <div class="w-10 rounded-full">
            <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}" />
        </div>
    </div>

    {{-- Nombre y correo: min-w-0 permite que truncate funcione dentro del flex --}}
    <div class="min-w-0 flex-1">
        <p class="truncate font-bold" title="{{ $user->full_name }}">
            {{ $user->full_name }}
        </p>
        <p class="truncate text-xs text-base-content/60" title="{{ $user->email }}">
            {{ $user->email }}
        </p>
    </div>

    {{-- Acciones --}}
    @isset($actions)
        <div {{ $actions->attributes->class(['shrink-0']) }}>
            {{ $actions }}
        </div>
    @endisset
</div>
