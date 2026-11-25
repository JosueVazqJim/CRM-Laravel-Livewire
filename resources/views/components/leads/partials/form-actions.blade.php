@props(['submitLabel' => 'Guardar', 'cancelRoute' => 'leads.index'])

<div class="flex justify-end items-center gap-4">
    {{-- Botón Cancelar --}}
    <x-mary-button
        label="Cancelar"
        icon="o-arrow-left"
        :link="route($cancelRoute)"
        class="btn-ghost"
    />

    {{-- Botón Guardar --}}
    <x-mary-button
        label="{{ $submitLabel }}"
        icon="o-check"
        type="submit"
        class="btn-primary"
        loading
    />
</div>

