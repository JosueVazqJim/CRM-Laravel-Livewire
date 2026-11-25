@props(['user' => null, 'userForm' => null, 'tabErrors' => [], 'isEdit' => false])

@php
    // Escoger qué prop fue pasada: preferimos userForm si existe (Update),
    // si no, usamos user (Create).
    $model = $userForm ?? $user;
    // Prefijo que usaremos en wire:model (debe coincidir con la propiedad pública del componente Livewire)
    $propName = $userForm ? 'userForm' : 'user';

    // Normalizar phones para foreach / count (funciona si phones es array, Collection u objeto)
    $phones = data_get($model, 'phones', []);
    if ($phones instanceof \Illuminate\Support\Collection) {
        $phones = $phones->toArray();
    }
    // Asegurar que $phones sea array
    $phones = is_array($phones) ? $phones : (array) $phones;

    $avatar = data_get($model, 'avatar', null);

    if (empty($avatar)) {
        $avatarSrc = asset('images/empty-user.jpg');
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
        } elseif (str_starts_with($avatar, 'avatars/')) {
            // Guardado típicamente como 'avatars/xxx' en disk public -> url /storage/avatars/xxx
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
            <x-mary-icon name="o-user" class="w-4 h-4" />
            <span>Usuario</span>
            @if($tabErrors['user'] ?? false)
                <x-mary-badge value="{{ $tabErrors['user'] }}" class="badge-error badge-sm"/>
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
                    crop-title-text="Ajusta tu foto de perfil"
                    crop-cancel-text="Cancelar"
                    crop-save-text="Guardar"
                    crop-after-change
                    class="w-full flex justify-center"
            >
                <div class="flex flex-col items-center w-full">
                    <span class="text-sm font-medium mb-2 text-center w-full">Foto de Perfil</span>
                    <div class="flex justify-center w-full mb-2">
                        <img src="{{ $avatarSrc }}" alt="Avatar de usuario" class="h-32 w-32 rounded-full object-cover border-4 border-base-200" />
                    </div>
                    <span class="text-xs text-gray-500 text-center w-full">Max 1MB, formatos: jpg, png</span>
                </div>
            </x-mary-file>
        </div>

        {{-- Grid responsive para datos personales --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <x-mary-input label="Nombre(s)" wire:model="{{ $propName }}.first_name" required class="input-primary"/>
            </div>

            <x-mary-input label="Apellido paterno" wire:model="{{ $propName }}.paternal_surname" class="input-primary"/>
            <x-mary-input label="Apellido materno" wire:model="{{ $propName }}.maternal_surname" class="input-primary"/>

            <x-mary-select label="Sexo" wire:model="{{ $propName }}.sex"
                           :options="[
                                ['id' => 'Masculino', 'name' => 'Masculino'],
                                ['id' => 'Femenino', 'name' => 'Femenino'],
                                ['id' => 'Otro', 'name' => 'Otro'],
                            ]"
                           placeholder="Seleccione una opción"
                           required class="input-primary" />
            <x-mary-input label="RFC" wire:model="{{ $propName }}.rfc" class="input-primary"/>

            <div class="sm:col-span-2">
                <x-mary-input label="Correo electrónico" wire:model="{{ $propName }}.email" type="email" required class="input-primary"/>
            </div>

            {{-- Teléfonos --}}
            <div class="sm:col-span-2">
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
                                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
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

                                            <div class="relative">
                                                <x-mary-select
                                                        label="Tipo de teléfono"
                                                        wire:model="{{ $propName }}.phones.{{ $index }}.type"
                                                        placeholder="Seleccionar tipo"
                                                        required
                                                        class="select-primary w-full"
                                                        :options="[
                                                        ['id' => 'mobile', 'name' => 'Móvil'],
                                                        ['id' => 'home', 'name' => 'Casa'],
                                                        ['id' => 'office', 'name' => 'Oficina'],
                                                        ['id' => 'other', 'name' => 'Otro'],
                                                    ]"
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
                </div>
            </div>

            {{-- Contraseñas --}}
            <div class="sm:col-span-2">
                <div class="flex items-center gap-2 mb-4">
                    <x-mary-icon name="o-lock-closed" class="w-5 h-5 text-primary" />
                    <h3 class="text-lg font-semibold">Seguridad</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-mary-password
                            label="Contraseña"
                            wire:model="{{ $propName }}.password"
                            hint="Mínimo 8 caracteres"
                            class="input-primary"
                            right
                    />

                    <x-mary-password
                            label="Confirmar contraseña"
                            wire:model="{{ $propName }}.password_confirmation"
                            class="input-primary"
                            right
                    />
                </div>
            </div>

        </div>
    </div>
</div>
