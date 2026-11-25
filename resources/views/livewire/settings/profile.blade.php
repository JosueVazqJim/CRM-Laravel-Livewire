<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Perfil')" :subheading="__('Actualiza tu información de perfil y dirección de correo electrónico.')">
        <x-mary-form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">

            {{-- Foto de perfil --}}
            <x-mary-file
                wire:model="avatar"
                accept="image/png, image/jpeg"
                change-text="Cambiar"
                crop-text="Recortar"
                crop-title-text="Ajusta tu foto de perfil"
                crop-cancel-text="Cancelar"
                crop-save-text="Guardar"
                crop-after-change
                class="w-full flex justify-center"
            >
                <div class="flex flex-col items-center w-full">
                    <span class="text-sm font-medium mb-2">Foto de perfil</span>
                    <img
                        src="{{ auth()->user()->avatar_url }}"
                        alt="Avatar"
                        class="h-32 w-32 rounded-full object-cover border-4 border-base-200 mb-2"
                    />
                    <span class="text-xs text-gray-500">Máx. 2MB — JPG o PNG</span>
                </div>
            </x-mary-file>

            {{-- Información personal --}}
            <div class="divider font-semibold text-sm">Información personal</div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-mary-input label="Nombre(s)" wire:model="first_name" required autocomplete="given-name" class="input-primary" />
                </div>
                <x-mary-input label="Apellido paterno" wire:model="paternal_surname" autocomplete="family-name" class="input-primary" />
                <x-mary-input label="Apellido materno" wire:model="maternal_surname" class="input-primary" />
                <x-mary-select
                    label="Sexo"
                    wire:model="sex"
                    :options="[
                        ['id' => 'Masculino', 'name' => 'Masculino'],
                        ['id' => 'Femenino',  'name' => 'Femenino'],
                        ['id' => 'Otro',      'name' => 'Otro'],
                    ]"
                    placeholder="Seleccione una opción"
                    required
                    class="select-primary"
                />
                <x-mary-input label="RFC" wire:model="rfc" maxlength="13" class="input-primary" />
            </div>

            {{-- Contacto --}}
            <div class="divider font-semibold text-sm">Contacto</div>

            <div>
                <x-mary-input label="Correo electrónico" wire:model="email" type="email" required autocomplete="email" class="input-primary" />

                @if ($this->hasUnverifiedEmail)
                    <div class="mt-4 text-sm">
                        <p class="text-gray-600 dark:text-gray-400">
                            {{ __('Su dirección de correo electrónico no está verificada.') }}
                        </p>
                        <x-mary-button
                            label="Reenviar verificación"
                            wire:click="resendVerificationNotification"
                            class="btn-primary btn-sm mt-2"
                            spinner="resendVerificationNotification"
                        />
                    </div>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-green-600 dark:text-green-400 text-sm font-medium">
                            {{ __('Se ha enviado un nuevo enlace de verificación a su dirección de correo electrónico.') }}
                        </p>
                    @endif
                @endif
            </div>

            {{-- Teléfonos --}}
            <div class="border-t pt-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <x-mary-icon name="o-phone" class="w-5 h-5 text-primary" />
                        <h3 class="text-base font-semibold">Teléfonos de contacto</h3>
                    </div>
                    <x-mary-badge value="{{ count($phones) }}" class="badge-primary badge-outline" />
                </div>

                <div class="space-y-3">
                    @foreach($phones as $index => $phone)
                        <div class="relative p-4 bg-base-200 rounded-box" wire:key="phone-{{ $index }}">
                            <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <x-mary-input
                                        label="Número telefónico"
                                        wire:model="phones.{{ $index }}.phone"
                                        placeholder="+52 222 123 4567"
                                        icon="o-phone"
                                        class="input-primary w-full"
                                    />
                                    <x-mary-select
                                        label="Tipo"
                                        wire:model="phones.{{ $index }}.type"
                                        placeholder="Seleccionar tipo"
                                        class="select-primary w-full"
                                        :options="[
                                            ['id' => 'mobile',  'name' => 'Móvil'],
                                            ['id' => 'home',    'name' => 'Casa'],
                                            ['id' => 'office',  'name' => 'Oficina'],
                                            ['id' => 'other',   'name' => 'Otro'],
                                        ]"
                                    />
                                </div>
                                @if(count($phones) > 1)
                                    <x-mary-button
                                        icon="o-trash"
                                        class="btn-ghost btn-sm text-error hover:bg-error/10 hover:border-error/20"
                                        wire:click="removePhone({{ $index }})"
                                        tooltip="Eliminar teléfono"
                                    />
                                @endif
                            </div>
                            @if($index === 0)
                                <span class="absolute -top-2 -right-2 badge badge-primary badge-sm">Principal</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    <x-mary-button
                        icon="o-plus-circle"
                        label="Agregar teléfono"
                        class="btn-primary btn-sm"
                        wire:click="addPhone()"
                    />
                </div>
            </div>

            {{-- Dirección --}}
            <div class="divider font-semibold text-sm">Dirección</div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-mary-input label="Tipo de dirección" wire:model="address.type" placeholder="Ej: Casa, Fiscal, Oficina" class="input-primary" />
                <x-mary-input label="Código Postal" wire:model="address.postal_code" class="input-primary" />

                <x-mary-select
                    label="Estado"
                    wire:model.live="address.state"
                    :options="$states"
                    placeholder="Selecciona un estado"
                    option-value="id"
                    option-label="name"
                    class="select-primary w-full"
                    searchable
                />

                <x-mary-select
                    label="Municipio"
                    wire:model="address.municipality"
                    :options="$municipalities"
                    placeholder="{{ empty($municipalities) ? 'Selecciona primero un estado' : 'Selecciona un municipio' }}"
                    :disabled="empty($municipalities)"
                    option-value="id"
                    option-label="name"
                    class="select-primary w-full"
                    searchable
                />

                <div class="sm:col-span-2">
                    <x-mary-input label="Colonia" wire:model="address.colony" class="input-primary" />
                </div>

                <x-mary-input label="Calle" wire:model="address.street" class="input-primary" />

                <div class="grid grid-cols-2 gap-3">
                    <x-mary-input label="No. Exterior" wire:model="address.exterior_number" class="input-primary" />
                    <x-mary-input label="No. Interior" wire:model="address.interior_number" class="input-primary" />
                </div>
            </div>

            <x-slot:actions>
                <x-mary-button label="Guardar" class="btn-primary" type="submit" spinner="updateProfileInformation" />
            </x-slot:actions>

        </x-mary-form>
    </x-settings.layout>
</section>
