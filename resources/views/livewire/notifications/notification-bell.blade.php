{{-- Mismo markup que <x-mary-menu-item> para que respete el sidebar colapsado (.mary-hideable) --}}
<li>
    <a href="{{ route('notifications.index') }}"
       wire:navigate
       @class([
           'my-0.5 py-1.5 px-4 hover:text-inherit whitespace-nowrap',
           'mary-active-menu bg-base-300' => request()->routeIs('notifications.*'),
       ])>

        {{-- Icono con badge (siempre visible, también colapsado) --}}
        <span class="relative block py-0.5">
            <x-mary-icon name="o-bell" class="mb-0.5" />

            @if($unreadCount > 0)
                <span class="absolute -top-1 -right-1.5">
                    <span class="relative flex h-4 w-4">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-error opacity-75"></span>
                        <span class="relative inline-flex items-center justify-center h-4 w-4 rounded-full bg-error text-white text-[9px] font-bold">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                    </span>
                </span>
            @endif
        </span>

        {{-- Texto y contador: se ocultan al colapsar --}}
        <span class="mary-hideable whitespace-nowrap truncate">
            Notificaciones

            @if($unreadCount > 0)
                <span class="badge badge-error badge-sm ml-1 text-white">{{ $unreadCount }}</span>
            @endif
        </span>
    </a>
</li>
