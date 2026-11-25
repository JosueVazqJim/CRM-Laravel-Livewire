<div class="space-y-6">
    {{-- Header --}}
    <x-mary-header title="Editar Usuario: {{ $user->getFullNameAttribute() }}" subtitle="Actualiza los datos del usuario." separator progress-indicator>
        <x-slot:actions>
            <x-mary-button label="Volver" icon="o-arrow-left" :link="route('users.show', $user)" class="btn-ghost" />
        </x-slot:actions>
    </x-mary-header>

    <div class="space-y-6">
        {{-- Tarjeta contenedora del formulario --}}
        <x-mary-card class="shadow-xl bg-base-200">
            <x-mary-form wire:submit="update" class="space-y-5" no-separator>

                {{-- Tabs con mejor scroll en móvil --}}
                <div class="w-full overflow-x-auto -mx-1 px-1">
                    <x-mary-tabs wire:model="tab"
                                 class="tabs-boxed flex-nowrap min-w-max pb-2">

                        {{-- Tab Usuario --}}
                        <x-mary-tab name="user">
                            <x-users.tabs.user-tab
                                    :userForm="$userForm"
                                    :tabErrors="$this->getTabErrors()"
                                    :isEdit="true"
                            />
                        </x-mary-tab>

                        {{-- Tab Empresas --}}
                        <x-mary-tab name="companies">
                            <x-users.tabs.companies-tab
                                    :companies="$companies"
                                    :companyOptions="$companyOptions"
                                    :tabErrors="$this->getTabErrors()"
                            />
                        </x-mary-tab>

                        {{-- Tab Dirección --}}
                        <x-mary-tab name="address">
                            <x-users.tabs.address-tab
                                    :address="$address"
                                    :states="$states"
                                    :municipalities="$municipalities"
                                    :loadingMunicipalities="$loadingMunicipalities"
                                    :tabErrors="$this->getTabErrors()"
                            />
                        </x-mary-tab>

                        <x-mary-tab name="roles">
                            <x-users.tabs.roles-tab
                                    :roles="$roles"
                                    :optionsRoles="$optionsRoles"
                                    :tabErrors="$this->getTabErrors()"
                            />
                        </x-mary-tab>

                    </x-mary-tabs>
                </div>

                {{-- Acciones del formulario --}}
                <x-users.partials.form-actions
                        submitLabel="Actualizar Usuario"
                        :cancelRoute="route('users.show', $user)"
                />

            </x-mary-form>
        </x-mary-card>
    </div>
</div>