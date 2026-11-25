@props(['serial'])

<x-mary-card class="shadow-lg">
    <x-slot:title class="flex items-center gap-2">
        <x-mary-icon name="o-newspaper" />
        Notas del Serial
    </x-slot:title>

    <div class="space-y-4">
        <div class="flex gap-4 items-start">
            <div class="flex-1">
                @if($serial->notes)
                    <p class="whitespace-pre-wrap">{{ $serial->notes }}</p>
                @else
                    <p class="text-base-content/50 italic">No hay notas para este serial.</p>
                @endif
            </div>
        </div>

        <div class="divider my-2"></div>

    </div>
</x-mary-card>

