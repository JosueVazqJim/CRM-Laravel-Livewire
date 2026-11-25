<div class="space-y-4 py-4">

    {{-- Versión padre --}}
    @if($parentVersion)
        <div>
            <p class="text-sm font-semibold text-base-content/60 mb-2">Revisión basada en</p>
            <div class="flex items-center gap-3 p-3 rounded-lg border border-base-300 hover:bg-base-200 transition">
                <x-mary-button label="" icon="o-arrow-up-left" :link="route('quotes.show', $parentVersion)" class="w-5 h-5 text-base-content/40" />
                <div>
                    <p class="font-mono font-semibold">{{ $parentVersion->folio }}</p>
                    <p class="text-xs text-base-content/50">Versión {{ $parentVersion->version_letter }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Versiones hijas --}}
    @if($childVersions->isNotEmpty())
        <div>
            <p class="text-sm font-semibold text-base-content/60 mb-2">
                Revisiones generadas desde esta versión
            </p>
            <div class="space-y-2">
                @foreach($childVersions as $child)
                    <div
                       class="flex items-center justify-between p-3 rounded-lg border border-base-300 hover:bg-base-200 transition
                              {{ $child->id === $currentVersion->id ? 'border-primary bg-primary/5' : '' }}">
                        <div class="flex items-center gap-3">
                            <x-mary-button label="" icon="o-arrow-down-right" :link="route('quotes.show', $child)" class="w-5 h-5 text-base-content/40" />

                            <div>
                                <p class="font-mono font-semibold">{{ $child->folio }}</p>
                                <p class="text-xs text-base-content/50">Versión {{ $child->version_letter }}</p>
                                <p class="text-xs text-base-content/50">Creada por {{ $child->quote->user->name }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold">${{ number_format($child->total, 2) }}</p>
                            <p class="text-xs text-base-content/50">{{ $child->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>

                @endforeach
            </div>
        </div>
    @endif

    {{-- Sin relaciones --}}
    @if(!$parentVersion && $childVersions->isEmpty())
        <p class="text-sm text-base-content/40 text-center py-6">
            Esta versión no tiene revisiones relacionadas.
        </p>
    @endif

</div>