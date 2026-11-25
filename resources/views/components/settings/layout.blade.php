@php
    // Estructura completa del menú
    $menuStructure = [
        [
            'title' => 'Perfil',
            'route' => 'profile.edit',
        ],
        [
            'title' => 'Contraseña',
            'route' => 'user-password.edit',
        ],
        [
            'title' => 'Two-factor auth',
            'route' => 'two-factor.show',
        ]
    ];
@endphp

<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <x-mary-menu activate-by-route>

            @foreach($menuStructure as $item)
                <x-mary-menu-item
                        :title="$item['title']"
                        :link="route($item['route'])"
                        wire:navigate
                />
            @endforeach
        </x-mary-menu>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">

        <x-mary-header size="text-l" :title="$heading ?? ''" :subtitle="$subheading ?? ''" separator />

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
