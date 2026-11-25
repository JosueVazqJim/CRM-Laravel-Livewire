<div class="space-y-6">
    {{-- Header --}}
    <x-mary-header title="Editar Empresa" subtitle="Actualiza los datos de la empresa." separator progress-indicator>
        <x-slot:actions>
            <x-mary-button label="Volver" icon="o-arrow-left" :link="route('companies.show', $company)" class="btn-ghost" />
        </x-slot:actions>
    </x-mary-header>

    <div class="space-y-6">
        {{-- Tarjeta contenedora del formulario --}}
        <x-mary-card class="shadow-xl bg-base-200">
            <x-mary-form wire:submit="update" class="space-y-5" no-separator>

                {{-- Tabs con mejor scroll en móvil --}}
                <x-mary-tabs wire:model="tab" class="tabs-boxed overflow-x-auto flex-nowrap pb-2">

                    {{-- Tab Empresa --}}
                    <x-mary-tab name="company">
                        <x-companies.tabs.company-tab
                                :companyForm="$companyForm"
                                :tabErrors="$this->getTabErrors()"
                        />
                    </x-mary-tab>

                    {{-- Tab Modulos --}}
{{--                    <x-mary-tab name="modules">--}}
{{--                        <x-companies.tabs.modules-tab--}}
{{--                                :modules="$modules"--}}
{{--                                :modulesOptions="$modulesOptions"--}}
{{--                                :tabErrors="$this->getTabErrors()"--}}
{{--                        />--}}
{{--                    </x-mary-tab>--}}

                    {{-- Tab Dirección --}}
                    <x-mary-tab name="address">
                        <x-companies.tabs.address-tab
                                :address="$address"
                                :states="$states"
                                :municipalities="$municipalities"
                                :loadingMunicipalities="$loadingMunicipalities"
                                :tabErrors="$this->getTabErrors()"
                        />
                    </x-mary-tab>

                    {{-- Tab Notas predeterminadas --}}
                    <x-mary-tab name="default_notes">
                        <x-companies.tabs.default-notes-tab
                                :defaultNotes="$defaultNotes"
                                :tabErrors="$this->getTabErrors()"
                        />
                    </x-mary-tab>

                </x-mary-tabs>

                {{-- Acciones del formulario --}}
                <x-companies.partials.form-actions
                        submitLabel="Actualizar Empresa"
                        :cancelRoute="route('companies.show', $company)"
                />

            </x-mary-form>

            {{-- Modal fuera del form principal para no interferir con wire:submit --}}
            <x-companies.modals.default-note-modal :editingNoteIndex="$editingNoteIndex" />

        </x-mary-card>
    </div>
</div>
