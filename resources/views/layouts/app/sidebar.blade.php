@php
    // Estructura completa del menú
    $menuStructure = [
        [
            'title' => 'Dashboard',
            'icon' => 'o-chart-pie',
            'route' => 'dashboard',
            'permission' => 'reportes.ver',
        ],
        [
            'title' => 'Cotizaciones',
            'icon' => 'o-gift',
            'route' => 'quotes.index',
            'permission' => 'cotizaciones.ver',
        ],
        [
            'title' => 'Usuarios',
            'icon' => 'o-user',
            'route' => 'users.index',
            'permission' => 'usuarios.ver',
        ],
        [
            'title' => 'Corportativo',
            'icon' => 'o-building-office',
            'route' => 'companies.index',
            'permission' => 'empresas.ver',
        ],
        [
            'title' => 'Leads',
            'icon' => 'o-building-storefront',
            'route' => 'leads.index',
            'permission' => 'leads.ver',
        ],
        [
            'title' => 'Warehouse',
            'icon' => 'o-wrench-screwdriver',
            'submenu' => [
                [
                    'title' => 'Productos',
                    'icon' => 'o-cube',
                    'route' => 'products.index',
                ],
            ],
            'permission' => 'productos.ver',
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen font-sans antialiased">
@livewireScripts

{{-- NAVBAR mobile only --}}
<x-mary-nav sticky class="lg:hidden">
    <x-slot:brand>
        <x-brand-custom.brand />
    </x-slot:brand>
    <x-slot:actions>
        <label for="main-drawer" class="lg:hidden mr-3">
            <x-mary-icon name="o-bars-3" class="cursor-pointer" />
        </label>
    </x-slot:actions>
</x-mary-nav>

{{-- MAIN --}}
<x-mary-main full-width>
    {{-- SIDEBAR --}}
    <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">
        <div class="hidden bg-primary/10 text-primary font-bold"></div>
        {{-- BRAND --}}
        <x-brand-custom.brand class="p-5" />

        {{-- MENU --}}
        <x-mary-menu activate-by-route title="" >

            {{-- User --}}
            @if($user = auth()->user())
                <x-mary-menu-separator />
                <x-users.avatar-card :user="$user">
                    {{-- Acciones --}}
                    <x-slot:actions>
                        <x-mary-dropdown right>
                            <x-slot:trigger>
                                <x-mary-button icon="o-cog-6-tooth" class="btn-circle btn-xs" />
                            </x-slot:trigger>

                            <x-mary-menu-item
                                    icon="o-cog-6-tooth"
                                    title="Ajustes"
                                    :link="route('profile.edit')"
                                    wire:navigate
                            />

                            <x-mary-theme-toggle class="hidden" />
                            <x-mary-menu-item title="Tema" icon="o-swatch" @click="$dispatch('mary-toggle-theme')" />

                            <x-mary-menu-separator/>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-mary-menu-item
                                        icon="o-power"
                                        title="Bye Bye"
                                        onclick="event.preventDefault(); this.closest('form').submit();"
                                />
                            </form>
                        </x-mary-dropdown>
                    </x-slot:actions>
                </x-users.avatar-card>

                <x-mary-menu-separator />
            @endif
{{--            <x-mary-button class="btn-primary"></x-mary-button>--}}
            @foreach($menuStructure as $item)
                @if(isset($item['permission']) && ! \Illuminate\Support\Facades\Gate::any((array) $item['permission']))
                    @continue
                @endif

                @if(isset($item['submenu']))
                    <x-mary-menu-sub :title="$item['title']" :icon="$item['icon']">
                        @foreach($item['submenu'] as $subItem)
                            <x-mary-menu-item
                                    :title="$subItem['title']"
                                    :icon="$subItem['icon']"
                                    :link="$subItem['route'] === '#' ? '#' : route($subItem['route'])"
                                    wire:navigate
                            />
                        @endforeach
                    </x-mary-menu-sub>
                @else
                    <x-mary-menu-item
                            :title="$item['title']"
                            :icon="$item['icon']"
                            :link="$item['route'] === '#' ? '#' : route($item['route'])"
                            wire:navigate
                    />
                @endif
            @endforeach
            <livewire:notifications.notification-bell />

        </x-mary-menu>
    </x-slot:sidebar>

    {{-- MAIN CONTENT --}}
    <x-slot:content>
        <div >
            {{ $slot }}
        </div>
    </x-slot:content>
</x-mary-main>

{{-- Toast --}}
<x-mary-toast />
@fluxScripts
</body>
</html>