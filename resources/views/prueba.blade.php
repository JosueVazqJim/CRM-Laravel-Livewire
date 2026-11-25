<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen font-sans antialiased bg-base-200">
@livewireScripts

{{-- NAVBAR mobile only --}}
<x-mary-nav sticky class="lg:hidden">
    <x-slot:brand>
        <x-brand-custom.brand />
    </x-slot:brand>
    <x-slot:actions>
        <label for="main-drawer" class="lg:hidden mr-3">
            <flux:icon.bolt variant="solid" />
        </label>
    </x-slot:actions>
</x-mary-nav>

{{-- MAIN --}}
<x-mary-main full-width>
    {{-- SIDEBAR --}}
    <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">

        {{-- BRAND --}}
        <x-brand-custom.brand class="p-5" />

        {{-- MENU --}}
        <x-mary-menu activate-by-route>

            {{-- User --}}
            @if($user = auth()->user())
                <x-mary-menu-separator />

                <x-mary-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 !-my-2 rounded">
                    <x-slot:actions>
                        <x-mary-button icon="o-power" class="btn-circle btn-ghost btn-xs" tooltip-left="logoff" no-wire-navigate link="/logout" />
                    </x-slot:actions>
                </x-mary-list-item>

                <x-mary-menu-separator />
            @endif

            <x-mary-menu-item
                    title="Hello"
                    icon="o-sparkles"
                    :link="route('prueba')"
            />
            <x-mary-menu-sub title="Settings" icon="o-cog-6-tooth">
                <x-mary-menu-item title="Wifi" icon="o-wifi" link="####" />
                <x-mary-menu-item title="Archives" icon="o-archive-box" link="####" />
            </x-mary-menu-sub>
        </x-mary-menu>
    </x-slot:sidebar>

    {{-- The `$slot` goes here --}}
    <x-slot:content>
        <div class="p-6">
            <h1>Prueba funcionando</h1>
        </div>
    </x-slot:content>
</x-mary-main>

{{-- Toast --}}
<x-mary-toast />
</body>

</html>
