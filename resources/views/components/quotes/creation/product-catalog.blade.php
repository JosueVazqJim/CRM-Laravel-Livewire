<x-mary-card class="shadow-xl bg-base-200">
    <x-mary-collapse separator style="border: none; box-shadow: none;">
        <x-slot:heading class="text-lg font-bold flex items-center gap-2">
            <x-mary-icon name="o-cube" class="w-5 h-5" />
            Catálogo de Productos
            <x-mary-badge value="{{$lead->company->name}}" class="badge-primary badge-sm" />
            @if(count($productTagFilter) > 0)
                <x-mary-badge value="{{ count($productTagFilter) }} etiqueta(s)" class="badge-accent badge-sm" />
            @endif
        </x-slot:heading>
        <x-slot:content>
            <div class="mb-4 flex flex-col md:flex-row gap-3">

                <x-mary-input
                        wire:model.live.debounce.400ms="productSearch"
                        icon="o-magnifying-glass"
                        placeholder="Buscar producto por nombre o SKU..."
                        class="input-sm input-primary w-full md:flex-1"
                        clearable
                />

                @if(count($productTagOptions) > 0)
                    <div class="w-full md:w-80">
                        <x-mary-choices-offline
                                wire:model.live="productTagFilter"
                                :options="$productTagOptions"
                                placeholder="Filtrar por etiqueta..."
                                no-result-text="No se encontraron etiquetas"
                                searchable
                                multiple
                                class="select-primary input-sm"
                        />
                    </div>
                @endif

            </div>



            @php($selectedProductIds = collect($items)->pluck('product_id')->all())

            <div class="max-h-[1000px] overflow-y-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pr-2">
                    @forelse($products as $product)
                        <div wire:key="catalog-product-{{ $product->id }}"
                             wire:click="toggleProduct({{ $product->id }})"
                             class="card card-compact bg-base-100 shadow-md border-2 transition-all duration-200 cursor-pointer
                                    {{ in_array($product->id, $selectedProductIds)
                                        ? 'border-primary bg-primary/5 dark:bg-primary/10'
                                        : 'border-base-300 hover:border-primary/50 dark:border-base-700' }}">
                            <div class="card-body p-4 relative">
                                @if(in_array($product->id, $selectedProductIds))
                                    <div class="absolute top-2 right-2">
                                        <x-mary-icon name="o-check-circle" class="w-6 h-6 text-primary" />
                                    </div>
                                @endif

                                <div class="flex justify-center mb-2">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
                                         class="h-45 w-auto object-cover rounded-lg">
                                </div>

                                <h3 class="font-bold text-sm text-gray-800 dark:text-gray-100 truncate"
                                    title="{{ $product->name }}">{{ $product->name }}</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-400">SKU: {{ $product->sku ?? 'N/A' }}</p>

                                <div class="mt-2 space-y-1">
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600 dark:text-gray-400 text-sm">Precio Regular:</span>
                                        <span class="font-semibold text-gray-800 dark:text-gray-200 text-sm">
                                            ${{ number_format($product->price_regular, 4) }}
                                        </span>
                                    </div>
{{--                                    <div class="flex justify-between text-xs">--}}
{{--                                        <span class="text-gray-600 dark:text-gray-400 text-sm">Costo de Instalación:</span>--}}
{{--                                        <span class="font-semibold text-secondary text-sm">--}}
{{--                                            ${{ number_format($product->installation_cost ?? 0, 4) }}--}}
{{--                                        </span>--}}
{{--                                    </div>--}}
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600 dark:text-gray-400 text-sm">Moneda:</span>
                                        <span class="font-semibold text-secondary text-sm">
                                            {{ $product->currency }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400">
                            <x-mary-icon name="o-exclamation-triangle" class="w-12 h-12 mx-auto mb-2" />
                            <p>No hay productos disponibles en el catálogo.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <x-mary-pagination
                    :rows="$products"
                    wire:model.live="productsPerPage"
                    :per-page-values="\App\Livewire\Quotes\Create::PRODUCTS_PER_PAGE_OPTIONS"
                    class="mt-4"
            />
        </x-slot:content>
    </x-mary-collapse>
</x-mary-card>