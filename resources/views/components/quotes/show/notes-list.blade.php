@props(['notes'])

<div class="card bg-base-100 shadow-sm">
    <div class="card-body">
        <h2 class="card-title">Notas de la Cotización</h2>
        <div class="space-y-3">
            @forelse($notes as $note)
                <div class="bg-base-200 p-4 rounded-lg">
                    <p class="whitespace-pre-wrap">{{ $note['note'] }}</p>
                    <div class="flex justify-between items-center mt-2 text-xs text-gray-500">
                        <span>{{ \Carbon\Carbon::parse($note['created_at'])->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <x-mary-icon name="o-document-text" class="w-12 h-12 mx-auto text-gray-400 mb-2" />
                    <p class="text-sm text-gray-500">No hay notas registradas</p>
                </div>
            @endforelse
        </div>
    </div>
</div>