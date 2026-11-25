<div class="card bg-base-100 border border-base-300 shadow-sm">
    <div class="card-body space-y-4">
        <div>
            <h3 class="text-lg font-semibold">Tasas de cambio</h3>
            <p class="text-sm text-base-content/60">Configura las conversiones utilizadas en la cotización.</p>
        </div>

        <div class="space-y-3">
            @foreach($quoteForm->currents as $rate)
                @if($rate['target_currency'] !== 'USD')
                    <div class="rounded-2xl border border-base-300 bg-base-200/30 px-4 py-3">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div class="flex flex-col">
                                <span class="font-medium text-base-content">
                                    {{ $rate['base_currency'] }} → {{ $rate['target_currency'] }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between gap-3 md:justify-end">
                                <span class="text-sm text-base-content/60">
                                    {{ number_format($rate['rate'], 2) }}
                                </span>
                                <input type="number"
                                       wire:change="updateExchangeRate('{{ $rate['target_currency'] }}', $event.target.value)"
                                       value="{{ $rate['rate'] }}"
                                       step="0.01"
                                       min="0"
                                       class="input input-bordered input-primary input-md w-28 text-right font-semibold" />
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>