<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<a href="{{ route('dashboard') }}" wire:navigate {{ $attributes }}>
    <div class="flex items-center content-center justify-center">
        <img
                src="{{ asset('logo_negro.svg') }}"
                class="block h-14 w-auto object-contain dark:hidden"
                alt="Logo"
        />
        <img
                src="{{ asset('logo_blanco.svg') }}"
                class="hidden h-14 w-auto object-contain dark:block"
                alt="Logo"
        />
    </div>
</a>