{{-- Logo según el tema: negro en claro, blanco en oscuro (clase `dark` en <html>) --}}
<img src="{{ asset('logo_negro.svg') }}" alt="Logo" class="block h-20 w-auto object-contain dark:hidden">
<img src="{{ asset('logo_blanco.svg') }}" alt="Logo" class="hidden h-20 w-auto object-contain dark:block">
