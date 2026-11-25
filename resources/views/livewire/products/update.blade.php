<div class="space-y-6">
    {{-- Header --}}
    <x-mary-header title="Editar Producto" subtitle="Actualiza los datos del producto." separator progress-indicator>
        <x-slot:actions>
            <x-mary-button label="Volver" icon="o-arrow-left" :link="route('products.show', $product)" class="btn-ghost" />
        </x-slot:actions>
    </x-mary-header>

    <div class="space-y-6">
        {{-- Tarjeta contenedora del formulario --}}
        <x-mary-card class="shadow-xl bg-base-200">
            <x-mary-form wire:submit="update" class="space-y-5" no-separator>

                {{-- Tabs --}}
                <div class="w-full overflow-x-auto -mx-1 px-1">
                    <x-mary-tabs wire:model="tab"
                                 class="tabs-boxed flex-nowrap min-w-max pb-2">

                    {{-- Tab Información Básica --}}
                        <x-mary-tab name="basic">
                            <x-products.tabs.basic-tab
                                :productForm="$productForm"
                                :typeOptions="$typeOptions"
                                :companyOptions="$companyOptions"
                                :categoryOptions="$categoryOptions"
                                :tabErrors="$this->getTabErrors()"
                                isEdit
                            />
                        </x-mary-tab>

                        {{-- Tab Precios --}}
                        <x-mary-tab name="pricing">
                            <x-products.tabs.pricing-tab
                                :productForm="$productForm"
                                :tabErrors="$this->getTabErrors()"
                            />
                        </x-mary-tab>

                        {{-- Tab Descripción e Imagen --}}
                        <x-mary-tab name="description">
                            <x-products.tabs.description-tab
                                :productForm="$productForm"
                                :tabErrors="$this->getTabErrors()"
                            />
                        </x-mary-tab>

                        {{-- Tab Avanzado --}}
                        <x-mary-tab name="advanced">
                            <x-products.tabs.advanced-tab
                                :productForm="$productForm"
                                :tagOptions="$tagOptions"
                                :tabErrors="$this->getTabErrors()"
                            />
                        </x-mary-tab>

                    </x-mary-tabs>
                </div>

                {{-- Acciones del formulario --}}
                <x-products.partials.form-actions
                    submitLabel="Actualizar Producto"
                    :cancelRoute="route('products.show', $product)"
                />

            </x-mary-form>
        </x-mary-card>
    </div>

    {{-- Modales de creación rápida — fuera del <form> para no interferir con su submit --}}
    <x-mary-modal wire:model="typeModal" title="Nuevo Tipo de Producto">
        <x-mary-input
            wire:model="newTypeName"
            label="Nombre del tipo"
            placeholder="Ej: Hardware, Software, Servicio..."
            autofocus
        />
        <x-slot:actions>
            <x-mary-button label="Cancelar" type="button"
                wire:click="$set('typeModal', false)" />
            <x-mary-button label="Guardar" type="button"
                wire:click="saveNewType"
                class="btn-primary"
                spinner="saveNewType" />
        </x-slot:actions>
    </x-mary-modal>

    <x-mary-modal wire:model="categoryModal" title="Nueva Categoría">
        <x-mary-input
            wire:model="newCategoryName"
            label="Nombre de la categoría"
            placeholder="Ej: Periféricos, Redes, Licencias..."
            autofocus
        />
        <x-slot:actions>
            <x-mary-button label="Cancelar" type="button"
                wire:click="$set('categoryModal', false)" />
            <x-mary-button label="Guardar" type="button"
                wire:click="saveNewCategory"
                class="btn-primary"
                spinner="saveNewCategory" />
        </x-slot:actions>
    </x-mary-modal>

    <x-mary-modal wire:model="tagModal" title="Nueva Etiqueta">
        <x-mary-input
            wire:model="newTagName"
            label="Nombre de la etiqueta"
            placeholder="Ej: Nuevo, Oferta, Destacado..."
            autofocus
        />
        <x-slot:actions>
            <x-mary-button label="Cancelar" type="button"
                wire:click="$set('tagModal', false)" />
            <x-mary-button label="Guardar" type="button"
                wire:click="saveNewTag"
                class="btn-primary"
                spinner="saveNewTag" />
        </x-slot:actions>
    </x-mary-modal>
</div>
