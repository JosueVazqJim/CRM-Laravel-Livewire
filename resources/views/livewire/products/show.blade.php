<div class="space-y-6">
    {{-- Header --}}
    <x-mary-header title="Detalles del Producto" subtitle="Información completa del producto seleccionado." separator>
        <x-slot:actions>
            <x-mary-button label="Volver" icon="o-arrow-left" :link="route('products.index')" class="btn-ghost" />
            @hasanyrole('admin')
            <x-mary-button label="Editar" icon="o-pencil" :link="route('products.update', $product)" class="btn-primary" />
            @endhasanyrole
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna izquierda --}}
        <div class="lg:col-span-1">
            <div class="sticky top-8 space-y-6">
                <x-products.show.product-image :product="$product" />
                <x-products.show.product-basic-info :product="$product" />
                <x-products.show.stock-info :product="$product" />
                <x-products.show.quick-actions :product="$product" :active="$active" />
            </div>
        </div>

        {{-- Columna derecha --}}
        <div class="lg:col-span-2 space-y-6">
            <x-products.show.product-pricing :product="$product" />
            <x-products.show.product-description :product="$product" />
            <x-products.show.product-specifications :product="$product" />
            <x-products.show.product-tags :product="$product" />
            <x-products.show.product-company :product="$product" />
        </div>
    </div>
</div>

