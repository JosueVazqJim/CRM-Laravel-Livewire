<div class="space-y-6">
    {{-- Header --}}
    <x-mary-header title="Crear Lead" subtitle="Completa todos los campos para crear un nuevo lead." separator progress-indicator>
        <x-slot:actions>
            <x-mary-button label="Volver" icon="o-arrow-left" :link="route('leads.index')" class="btn-ghost" />
        </x-slot:actions>
    </x-mary-header>

    <div class="space-y-6">
        {{-- Tarjeta contenedora del formulario --}}
        <x-mary-card class="shadow-xl bg-base-200">
            <x-mary-form wire:submit="save" class="space-y-5" no-separator>
                {{-- Wrapper que controla el scroll de los tabs --}}
                <div class="w-full overflow-x-auto -mx-1 px-1">
                    <x-mary-tabs wire:model="tab" class="tabs-boxed flex-nowrap min-w-max pb-2">

                        {{-- Tab Información General --}}
                        <x-mary-tab name="general" label="Información General">
                            <x-leads.tabs.general
                                :company_id="$company_id"
                                :name_company="$name_company"
                                :state_id="$state_id"
                                :last_contact="$last_contact"
                                :companyOptions="$companyOptions"
                                :states="$states"
                                :tabErrors="$this->getTabErrors()"
                            />
                        </x-mary-tab>

                        {{-- Tab Información Comercial --}}
                        <x-mary-tab name="commercial" label="Información Comercial">
                            <x-leads.tabs.commercial
                                :urgency_level="$urgency_level"
                                :budget="$budget"
                                :undermine_channel="$undermine_channel"
                                :classification="$classification"
                                :funnel_stage="$funnel_stage"
                                :probability="$probability"
                                :tabErrors="$this->getTabErrors()"
                            />
                        </x-mary-tab>

                        {{-- Tab Estado del Cliente --}}
                        <x-mary-tab name="client_status" label="Estado del Cliente">
                            <x-leads.tabs.client-status
                                :client_progress="$client_progress"
                                :client_status="$client_status"
                                :pay_status="$pay_status"
                                :after_sales="$after_sales"
                                :tabErrors="$this->getTabErrors()"
                            />
                        </x-mary-tab>

                        {{-- Tab Diagnóstico y Proceso --}}
                        <x-mary-tab name="process" label="Diagnóstico y Proceso">
                            <x-leads.tabs.process
                                :problem_detected="$problem_detected"
                                :demo_offered="$demo_offered"
                                :first_contact="$first_contact"
                                :differentiator_explained="$differentiator_explained"
                                :diagnosis_made="$diagnosis_made"
                                :tabErrors="$this->getTabErrors()"
                            />
                        </x-mary-tab>

                        {{-- Tab Facturación --}}
                        <x-mary-tab name="billing" label="Facturación">
                            <x-leads.tabs.billing
                                :business_name="$business_name"
                                :rfc="$rfc"
                                :billing_email="$billing_email"
                                :address_state="$address_state"
                                :municipality="$municipality"
                                :colony="$colony"
                                :street="$street"
                                :exterior_number="$exterior_number"
                                :interior_number="$interior_number"
                                :postal_code="$postal_code"
{{--                                :states="$states"--}}
{{--                                :municipalities="$municipalities"--}}
{{--                                :loadingMunicipalities="$loadingMunicipalities"--}}
                                :tabErrors="$this->getTabErrors()"
                            />
                        </x-mary-tab>

                        {{-- Tab Contactos --}}
                        <x-mary-tab name="contacts" label="Contactos">
                            <x-leads.tabs.contacts
                                :contacts="$contacts"
                                :tabErrors="$this->getTabErrors()"
                            />
                        </x-mary-tab>

                        {{-- Tab Actividades --}}
{{--                        <x-mary-tab name="activities" label="Actividades">--}}
{{--                            <x-leads.tabs.activities--}}
{{--                                :activities="$activities"--}}
{{--                                :tabErrors="$this->getTabErrors()"--}}
{{--                            />--}}
{{--                        </x-mary-tab>--}}

                        {{-- Tab Acceso de Usuarios --}}
                        <x-mary-tab name="user_access" label="Acceso de Usuarios">
                            <x-leads.tabs.user-access
                                :selectedUsers="$selectedUsers"
                                :userOptions="$userOptions"
                                :tabErrors="$this->getTabErrors()"
                            />
                        </x-mary-tab>

                    </x-mary-tabs>
                </div>

                {{-- Acciones del formulario --}}
                <x-leads.partials.form-actions
                    submitLabel="Crear Lead"
                    cancelRoute="leads.index"
                />

            </x-mary-form>
        </x-mary-card>
    </div>
</div>

