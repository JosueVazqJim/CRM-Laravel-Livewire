<div class="space-y-6">
    {{-- Banner contextual para revisión / recotización --}}
    @if($prefillMode)
        <div class="alert {{ $prefillMode === 'revision' ? 'alert-info' : 'alert-warning' }}">
            <x-mary-icon name="{{ $prefillMode === 'revision' ? 'o-document-duplicate' : 'o-arrow-path' }}" class="w-5 h-5" />
            <span>
                {{ $prefillMode === 'revision' ? 'Revisión de: ' : 'Recotización de: ' }}
                <strong>{{ $sourceFolio }}</strong>
                — {{ $prefillMode === 'revision' ? 'Las tasas de cambio son las del momento original. Ajusta los datos antes de guardar.' : 'Se cargaron tasas de cambio actualizadas. Revisa los precios convertidos antes de guardar.' }}
            </span>
        </div>
    @endif

    {{-- Header --}}
    <x-mary-header title="Crear cotización para {{$lead->name_company ?? 'N/A'}}" subtitle="Ingresa los datos de la nueva cotización." separator progress-indicator>
        <x-slot:actions>
            <x-mary-button label="Volver" icon="o-arrow-left" :link="route('leads.show', $lead)" class="btn-ghost" />
        </x-slot:actions>
    </x-mary-header>

    <div class="space-y-6">
        <div class="space-y-6">
            <x-quotes.creation.quote-info-card :$lead :quoteForm="$quoteForm" />

            <x-quotes.creation.product-catalog
                    :$lead
                    :$products
                    :$items
                    :$productSearch
                    :$productTagOptions
                    :productTagFilter="$productTagFilter"
            />

            <x-quotes.creation.selected-products-table
                    :$items
                    :$quoteForm
                    :$lead
                    :selectedCurrencies="$selectedCurrencies"
                    :itemsTotalNoDiscount="$itemsTotalNoDiscount"
                    :itemsTotalDiscount="$itemsTotalDiscount"
                    :quoteForm="$quoteForm"
                    :subtotal="$subtotal"
                    :tax="$tax"
                    :total="$total"
                    :concepts="$concepts"
            />
        </div>

        <x-quotes.creation.form-actions :$lead :defaultNotes="$defaultNotes" />
    </div>
</div>

