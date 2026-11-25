@props(['company' => null, 'companyForm' => null, 'tabErrors' => [], 'isEdit' => false])

@php
    // Escoger qué prop fue pasada: preferimos userForm si existe (Update),
    // si no, usamos user (Create).
    $model = $companyForm ?? $company;
    // Prefijo que usaremos en wire:model (debe coincidir con la propiedad pública del componente Livewire)
    $propName = $companyForm ? 'companyForm' : 'company';

    // Normalizar phones para foreach / count (funciona si phones es array, Collection u objeto)
    $phones = data_get($model, 'phones', []);
    if ($phones instanceof \Illuminate\Support\Collection) {
        $phones = $phones->toArray();
    }
    // Asegurar que $phones sea array
    $phones = is_array($phones) ? $phones : (array) $phones;

    // Normalizar emails para foreach / count (funciona si emails es array, Collection u objeto)
    $emails = data_get($model, 'emails', []);
    if ($emails instanceof \Illuminate\Support\Collection) {
        $emails = $emails->toArray();
    }
    // Asegurar que $emails sea array
    $emails = is_array($emails) ? $emails : (array) $emails;

    $avatar = data_get($model, 'avatar', null);

    if (empty($avatar)) {
        $avatarSrc = asset('images/no-image.jpg');
    } elseif (filter_var($avatar, FILTER_VALIDATE_URL) || str_starts_with($avatar, 'http')) {
        // Ya es una URL completa (p. ej. https://...)
        $avatarSrc = $avatar;
    } else {
        // Normalizar prefijos y generar URL accesible desde public
        $avatar = ltrim($avatar, '/');

        // Si viene con 'public/' delante, quitarlo (p.ej. 'public/storage/avatars/..')
        if (str_starts_with($avatar, 'public/')) {
            $avatar = substr($avatar, 7);
        }

        // Si ya está en 'storage/...' usamos asset()
        if (str_starts_with($avatar, 'storage/')) {
            $avatarSrc = asset($avatar);
        } elseif (str_starts_with($avatar, 'logos/')) {
            // Guardado típicamente como 'avatars/xxx' en disk public -> url /storage/logos/xxx
            $avatarSrc = asset('storage/' . $avatar);
        } else {
            // fallback genérico
            $avatarSrc = asset($avatar);
        }
    }
@endphp

<div>
    <x-slot:label>
        <div class="flex items-center gap-1 whitespace-nowrap">
            <x-mary-icon name="o-building-office" class="w-4 h-4" />
            <span>Empresa</span>
            @if($tabErrors['company'] ?? false)
                <x-mary-badge value="{{ $tabErrors['company'] }}" class="badge-error badge-sm"/>
            @endif
        </div>
    </x-slot:label>

    <div class="space-y-5 p-1">
        {{-- Foto de perfil --}}
        <div class="w-full">
            <x-mary-file
                    label=" "
                    wire:model="{{ $propName }}.avatar"
                    hint=" "
                    accept="image/png, image/jpeg"
                    change-text="Cambiar"
                    crop-text="Recortar"
                    crop-title-text="Ajusta el logo de la empresa"
                    crop-cancel-text="Cancelar"
                    crop-save-text="Guardar"
                    crop-after-change
                    class="w-full flex justify-center"
            >
                <div class="flex flex-col items-center w-full">
                    <span class="text-sm font-medium mb-2 text-center w-full">Logo de la Empresa</span>
                    <div class="flex justify-center w-full mb-2">
                        <img src="{{ $avatarSrc }}" alt="Logo de la empresa" class="h-50 w-70 object-cover border-4 border-base-200" />
                    </div>
                    <span class="text-xs text-gray-500 text-center w-full">Max 10MB, formatos: jpg, png</span>
                </div>
            </x-mary-file>
        </div>

        {{-- Grid responsive para datos personales --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <x-mary-input label="Nombre de la empresa" wire:model="{{ $propName }}.name" required class="input-primary"/>
            </div>

            <x-mary-input label="Abreviación" wire:model="{{ $propName }}.slug" required class="input-primary"/>
            <x-mary-input label="Sitio Web" wire:model="{{ $propName }}.website" class="input-primary"/>

            <x-mary-textarea label="Descripción" wire:model="{{ $propName }}.description" placeholder="Descripción breve de la empresa..."
                             hint="Max 10000 caracteres" rows="5" class="input-primary" />

            <x-mary-colorpicker wire:model="{{ $propName }}.theme" label="Color del tema" suffix="Hex code" class="input-primary"/>

            {{-- Teléfonos --}}
            <div class="">
                <div class="mt-8 border-t pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <x-mary-icon name="o-phone" class="w-5 h-5 text-primary" />
                            <h3 class="text-lg font-semibold">Teléfonos de contacto</h3>
                        </div>

                        <x-mary-badge value="{{ count($phones) }}" class="badge-primary badge-outline" />
                    </div>

                    <div class="space-y-3">
                        @foreach($phones as $index => $phone)
                            <div class="group relative">
                                <div class="p-4 shadow-xl bg-gradient-to-br from-base-100 to-base-200" wire:key="phone-row-{{ $index }}">
                                    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                                        <div class="flex-1 gap-3">
                                            <div class="relative">
                                                <x-mary-input
                                                        label="Número telefónico"
                                                        wire:model="{{ $propName }}.phones.{{ $index }}.phone"
                                                        placeholder="+52 222 123 4567"
                                                        class="input-primary w-full"
                                                        required
                                                        icon="o-phone"
                                                />
                                            </div>
                                        </div>

                                        @if(count($phones) > 1)
                                            <div class="flex justify-end sm:self-center">
                                                <x-mary-button
                                                        icon="o-trash"
                                                        class="btn-ghost btn-sm text-error hover:bg-error/10 hover:text-error border border-transparent hover:border-error/20"
                                                        wire:click="removePhone({{ $index }})"
                                                        tooltip="Eliminar teléfono"
                                                />
                                            </div>
                                        @endif
                                    </div>

                                    @if($index === 0)
                                        <div class="absolute -top-2 -right-2">
                                            <span class="badge badge-primary badge-sm">Principal</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 flex justify-center sm:justify-start">
                        <x-mary-button
                                icon="o-plus-circle"
                                label="Agregar otro teléfono"
                                class="btn-primary btn-sm gap-2 hover:gap-3 transition-all"
                                wire:click="addPhone()"
                        />
                    </div>

                    <p class="text-xs text-primary mt-3 flex items-center gap-1">
                        <x-mary-icon name="o-information-circle" class="w-4 h-4" />
                        Puedes agregar múltiples teléfonos para diferentes tipos de contacto
                    </p>
                </div>
            </div>

            {{-- Emails --}}
            <div class="">
                <div class="mt-8 border-t pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <x-mary-icon name="o-phone" class="w-5 h-5 text-primary" />
                            <h3 class="text-lg font-semibold">Direcciones de correos electrónicos</h3>
                        </div>

                        <x-mary-badge value="{{ count($emails) }}" class="badge-primary badge-outline" />
                    </div>

                    <div class="space-y-3">
                        @foreach($emails as $index => $email)
                            <div class="group relative">
                                <div class="p-4 shadow-xl bg-gradient-to-br from-base-100 to-base-200" wire:key="email-row-{{ $index }}">
                                    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                                        <div class="flex-1 gap-3">
                                            <div class="relative">
                                                <x-mary-input
                                                        label="Correo electrónico"
                                                        wire:model="{{ $propName }}.emails.{{ $index }}.email"
                                                        placeholder="example@email.com"
                                                        class="input-primary w-full"
                                                        required
                                                        icon="o-envelope"
                                                />
                                            </div>
                                        </div>

                                        @if(count($emails) > 1)
                                            <div class="flex justify-end sm:self-center">
                                                <x-mary-button
                                                        icon="o-trash"
                                                        class="btn-ghost btn-sm text-error hover:bg-error/10 hover:text-error border border-transparent hover:border-error/20"
                                                        wire:click="removeEmail({{ $index }})"
                                                        tooltip="Eliminar Correo"
                                                />
                                            </div>
                                        @endif
                                    </div>

                                    @if($index === 0)
                                        <div class="absolute -top-2 -right-2">
                                            <span class="badge badge-primary badge-sm">Principal</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 flex justify-center sm:justify-start">
                        <x-mary-button
                                icon="o-plus-circle"
                                label="Agregar otro correo electrónico"
                                class="btn-primary btn-sm gap-2 hover:gap-3 transition-all"
                                wire:click="addEmail()"
                        />
                    </div>

                    <p class="text-xs text-primary mt-3 flex items-center gap-1">
                        <x-mary-icon name="o-information-circle" class="w-4 h-4" />
                        Puedes agregar múltiples direcciones de correo
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>
