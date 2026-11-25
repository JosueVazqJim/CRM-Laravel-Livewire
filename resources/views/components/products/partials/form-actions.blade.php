@props(['submitLabel' => 'Guardar', 'cancelRoute' => 'products.index'])

<div class="flex flex-col sm:flex-row gap-3 justify-between items-center pt-6 border-t mt-6">
    <x-mary-button label="Cancelar" icon="o-x-mark" :link="$cancelRoute" class="btn-ghost" />

    <div class="flex gap-2">
        <x-mary-button label="{{ $submitLabel }}" icon="o-check" class="btn-primary" type="submit" spinner />
    </div>
</div>

