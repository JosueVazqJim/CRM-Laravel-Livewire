@props(['product'])

<x-mary-card class="shadow-lg">
    <div class="space-y-4">
        <div class="flex items-center gap-2 mb-4">
            <x-mary-icon name="o-building-office" class="w-6 h-6 text-gray-800 dark:text-gray-400" />
            <h3 class="text-lg font-semibold">Empresa</h3>
        </div>

        <div class="space-y-3 text-sm">
            <div class="flex items-center gap-3 p-3 border border-base-300 rounded-lg">
                <div class="flex-1">
                    <p class="font-medium">{{ $product->company->name }}</p>
                </div>
                <x-mary-button
                        icon="o-arrow-right"
                        class="btn-ghost btn-sm"
                        :link="url('companies/' . $product->company->slug)"
                />
            </div>
        </div>
    </div>
</x-mary-card>

