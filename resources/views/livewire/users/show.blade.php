<div class="space-y-6">
    {{-- Header --}}
    <x-mary-header title="Detalles del Usuario" subtitle="Información completa del usuario seleccionado." separator>
        <x-slot:actions>
            <x-mary-button label="Volver" icon="o-arrow-left" :link="route('users.index')" class="btn-ghost" />
            <x-mary-button label="Editar" icon="o-pencil" :link="route('users.update', $user)" class="btn-primary" />
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna izquierda --}}
        <div class="lg:col-span-1">
            <div class="sticky top-8 space-y-6">
                <x-users.show.user-info :user="$user" :roles="$roles" />
                <x-users.show.quick-actions :user="$user" :active="$active" />
            </div>
        </div>

        {{-- Columna derecha --}}
        <div class="lg:col-span-2 space-y-6">
            <x-users.show.phones-info :user="$user" />
            <x-users.show.companies-info :user="$user" />
            <x-users.show.address-info
                    :user="$user"
                    :stateName="$stateName"
                    :municipalityName="$municipalityName"
            />
        </div>
        <span class="ml-3">
</span>

    </div>
</div>