<x-mary-card class="shadow-xl bg-gradient-to-br from-base-100 to-base-200">
    <x-slot:title>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-mary-icon name="o-information-circle" class="h-5 w-5" />
                <span>Información General</span>
            </div>

            @if(!$editing)
                <x-mary-button
                        icon="o-pencil"
                        label="Editar"
                        wire:click="enableEditing"
                        class="btn-sm"
                />
            @endif
        </div>
    </x-slot:title>

    <div class="space-y-4">
        @if($editing)
            {{-- Nombre de Empresa --}}
            <div>
                <x-mary-input
                    wire:model="name_company"
                    label="Empresa Cliente"
                    class="input-primary"
                />
            </div>

            {{-- Empresa Corporativa --}}
            @if($lead->company)
                <div>
                    <x-mary-choices-offline
                            label="Selecciona la empresa del corporativo."
                            wire:model="company_id"
{{--                            wire:model.live.debounce="company"--}}
                            :options="$companyOptions"
                            no-result-text="No se encontraron empresas"
                            single
                            searchable
                            class="select-primary"
                    />
                </div>
            @endif

            {{-- Estado --}}
            <div>
                <x-mary-select
                    wire:model="state_id"
                    label="Estado"
                    :options="$states"
                    option-value="id"
                    option-label="name"
                    placeholder="Selecciona un estado..."
                    class="select-primary"
                    searchable
                    clearable
                />
            </div>

            <div>
                <x-mary-datetime
                    label="Fecha de último contacto"
                    wire:model="last_contact"
                    type="datetime-local"
                    class="input-primary"
                />
            </div>

            <div class="flex gap-2 justify-end">
                <x-mary-button
                        label="Cancelar"
                        wire:click="cancelEditing"
                        wire:loading.attr="disabled"
                        wire:target="cancelEditing"
                        class="btn-ghost"
                />

                <x-mary-button
                        label="Guardar"
                        wire:click="update"
                        wire:loading.attr="disabled"
                        wire:target="update"
                        class="btn-primary"
                />
            </div>
        @else
            {{-- Nombre de Empresa --}}
            <div>
                <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Empresa Cliente</label>
                <div class="text-base font-medium">{{ $lead->name_company ?? 'Sin empresa' }}</div>
            </div>

            {{-- Empresa Corporativa --}}
            @if($lead->company)
                <div>
                    <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Empresa Corporativa</label>
                    <div class="flex items-center gap-2">
                        <x-mary-avatar
                                :image="$lead->company->avatar_url"
                                class="!w-8 !h-8"
                        />
                        <span class="font-medium">{{ $lead->company->name }}</span>
                    </div>
                </div>
            @endif

            {{-- Usuario Creador --}}
            @if($lead->user)
                <div>
                    <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Creado por</label>
                    <div class="flex items-center gap-2">
                        <x-mary-avatar
                                :image="$lead->user->avatar_url"
                                class="!w-8 !h-8"
                        />
                        <span class="font-medium">
                        {{ $lead->user->first_name }}
                            {{ $lead->user->paternal_surname }}
                            {{ $lead->user->maternal_surname }}
                    </span>
                    </div>
                </div>
            @endif

            {{-- Estado --}}
            <div>
                <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Estado</label>
                <div class="font-medium">{{ $lead->state->name ?? 'No disponible' }}</div>
            </div>
        @endif

        {{-- Fecha de Creación --}}
        <div>
            <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Creado el</label>
            <div class="font-medium">
                {{ $lead->creat_at?->format('d/m/Y H:i') ?? 'No disponible' }}
            </div>
        </div>

        {{-- Fecha de ultimo contacto --}}
        <div>
            <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Fecha de último contacto</label>
            <div class="font-medium">
                {{ $lead->last_contact?->format('d/m/Y H:i') ?? 'No disponible' }}
            </div>
        </div>
    </div>
</x-mary-card>

