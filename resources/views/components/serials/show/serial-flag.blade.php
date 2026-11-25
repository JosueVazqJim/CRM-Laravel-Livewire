@props(['serial'])

<x-mary-card class="shadow-lg">
    <x-slot:title class="flex items-center gap-2">
        <x-mary-icon name="o-flag" />
        Bandera
    </x-slot:title>

    <div class="space-y-4">
        <div class="flex items-center gap-3">
            @if($serial->flag_color)
                <div class="w-10 h-10 rounded border-2" style="background-color: {{ $serial->flag_color }}; border-color: {{ $serial->flag_color }};"></div>
                <div>
                    <p class="text-sm text-base-content/60">Color</p>
                    <p class="font-mono font-semibold">{{ strtoupper($serial->flag_color) }}</p>
                </div>
            @else
                <p class="text-base-content/50 italic">Sin bandera asignada</p>
            @endif
        </div>

        <div class="divider my-2"></div>


    </div>
</x-mary-card>

