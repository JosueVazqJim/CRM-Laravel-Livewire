<div class="space-y-6">
    {{-- Header --}}
    <x-mary-header title="Detalles del Serial" subtitle="Información completa del serial seleccionado." separator>
        <x-slot:actions>
            <x-mary-button label="Volver" icon="o-arrow-left" :link="route('products.serials.index', $serial->product)" class="btn-ghost" />
            @can('seriales.editar')
            <x-mary-button label="Editar" icon="o-pencil" :link="route('products.serials.update', $serial)" class="btn-primary" />
            @endcan
        </x-slot:actions>
    </x-mary-header>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna izquierda --}}
        <div class="lg:col-span-1">
            <div class="sticky top-8 space-y-6">
                <x-serials.show.serial-basic-info :serial="$serial" />
{{--                <x-serials.show.serial-status :serial="$serial" />--}}
                <livewire:serials.sale-info :serial="$serial" wire:key="sale-info-{{ $serial->id }}" />
                <livewire:serials.status-transition :serial="$serial" wire:key="status-transition-{{ $serial->id }}" />
{{--                <x-serials.show.serial-flag :serial="$serial" />--}}
            </div>
        </div>
        {{-- Columna derecha --}}
        <div class="lg:col-span-2 space-y-6">
            <x-serials.show.serial-product-info :serial="$serial" />
            <x-serials.show.serial-dates :serial="$serial" />
            <x-serials.show.serial-pricing :serial="$serial" />
            <x-serials.show.serial-notes :serial="$serial" />
            <x-serials.show.serial-movements :serial="$serial" />
        </div>
    </div>
</div>
