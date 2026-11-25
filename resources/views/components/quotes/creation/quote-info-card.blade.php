<x-mary-card class="shadow-xl bg-base-200">
    <x-slot:title class="text-lg font-bold flex items-center gap-2">
        <x-mary-icon name="o-building-office" class="w-5 h-5" />
        Información de la Cotización
    </x-slot:title>

    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Empresa --}}
            <div class="group">
                <span class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">
                    Empresa del corporativo
                </span>
                <div class="flex items-center gap-4 mt-1">
                    <img src="{{ $lead->company->avatar_url }}"
                         alt="{{ $lead->company->name }}"
                         class="w-60 h-25 rounded-2xl object-cover border-2 border-primary/20 shadow-md">
                    <div>
                        <div class="font-bold text-gray-800 dark:text-gray-200 text-xl">{{ $lead->company->name }}</div>
                        <div class="text-sm text-gray-500 mt-1">Corporativo</div>
                    </div>
                </div>
            </div>

            {{-- Costo de Envío --}}
            <div class="space-y-2">
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                    <x-mary-icon name="o-truck" class="w-4 h-4 text-primary" />
                    Costo de Envío
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-400 sm:text-sm">$</span>
                    </div>
                    <x-mary-input
                            wire:model.live="quoteForm.shipping_price"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            class="input-primary pl-7"
                            prefix="USD"
                    />
                </div>
            </div>
        </div>

        <x-quotes.creation.exchange-rates-card :quoteForm="$quoteForm" />
    </div>
</x-mary-card>