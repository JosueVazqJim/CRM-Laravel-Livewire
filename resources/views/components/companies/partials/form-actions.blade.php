@props(['submitLabel' => 'Guardar', 'cancelRoute' => 'users.index'])

<div class="pt-6 border-t border-base-200">
    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3">
        <x-mary-button
                label="Cancelar"
                :link="$cancelRoute"
                class="btn-ghost"
        />

        <x-mary-button
                label="{{ $submitLabel }}"
                type="submit"
                icon="o-check"
                class="btn-primary"
                spinner="save"
        />
    </div>
</div>