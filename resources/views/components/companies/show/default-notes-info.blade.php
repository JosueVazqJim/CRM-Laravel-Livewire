@props(['company'])

<x-mary-card class="shadow-xl">
    <x-slot:title>
        <div class="flex items-center gap-2">
            <x-mary-icon name="o-document-text" class="w-5 h-5" />
            <span>Notas predeterminadas</span>
            <x-mary-badge value="{{ $company->defaultNotes->count() }}" class="badge-primary badge-md" />
        </div>
    </x-slot:title>

    <x-slot:subtitle>
        Notas disponibles al crear cotizaciones para esta empresa
    </x-slot:subtitle>

    @if($company->defaultNotes->count() > 0)
        <div class="space-y-3">
            @foreach($company->defaultNotes as $note)
                <div class="border border-base-300 rounded-lg p-4">
                    <p class="font-semibold text-sm">{{ $note->title }}</p>
                    <p class="text-xs text-gray-500 mt-1 whitespace-pre-line">{{ $note->content }}</p>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-8 text-gray-500">
            <x-mary-icon name="o-document-text" class="w-12 h-12 mb-3 opacity-50" />
            <p>No hay notas predeterminadas para esta empresa</p>
        </div>
    @endif
</x-mary-card>
