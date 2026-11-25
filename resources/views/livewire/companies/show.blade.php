<div class="space-y-6">
    {{-- Header --}}
    <x-mary-header title="Detalles de la Empresa" subtitle="Información completa de la empresa de RAMA seleccionada." separator>
        <x-slot:actions>
            <x-mary-button label="Volver" icon="o-arrow-left" :link="route('companies.index')" class="btn-ghost" />
            <x-mary-button label="Editar" icon="o-pencil" :link="route('companies.update', $company)" class="btn-primary" />
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna izquierda --}}
        <div class="lg:col-span-1">
            <div class="sticky top-8 space-y-6">
                <x-companies.show.company-info :company="$company" />
                <x-companies.show.modules-info :company="$company" />
                <x-companies.show.quick-actions :company="$company" :active="$active" />
            </div>
        </div>

        {{-- Columna derecha --}}
        <div class="lg:col-span-2 space-y-6">
            <x-companies.show.phones-info :company="$company" />
            <x-companies.show.emails-info :company="$company" />
            <x-companies.show.address-info
                    :company="$company"
                    :stateName="$stateName"
                    :municipalityName="$municipalityName"
            />
            <x-companies.show.users-info :company="$company" />
            <x-companies.show.default-notes-info :company="$company" />
        </div>
        <span class="ml-3">
</span>

    </div>
</div>