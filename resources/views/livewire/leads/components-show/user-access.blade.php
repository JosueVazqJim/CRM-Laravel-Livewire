    <x-mary-card class="shadow-xl">
        <x-slot:title>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-mary-icon name="o-document-text" class="h-5 w-5" />
                    <span>Accesos de Usuario</span>
                    <x-mary-badge value="{{ $lead->userAccess->count() }}" class="badge-primary badge-sm" />
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

        <div class="space-y-2">
            @if($editing)
                <div>
                    <x-mary-choices-offline
                            label="Usuarios Vendedores"
                            wire:model.live.debounce="selectedUsers"
                            :options="$userOptions"
                            no-result-text="No se encontraron usuarios"
                            clearable
                            searchable
                            class="w-full select-primary "
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
                @if($lead->userAccess->count() > 0)

                @foreach($lead->userAccess as $access)
                    <div class="flex items-center justify-between p-3 bg-base-100 rounded border border-base-300 hover:border-primary transition-colors">
                        <div class="flex items-center gap-2">
                            <x-mary-avatar
                                    :image="$access->user?->avatar_url ?? asset('images/empty-user.jpg')"
                                    class="!w-8 !h-8"
                            />
                            <div>
                                <p class="font-semibold text-sm">
                                    {{ $access->user?->first_name }}
                                    {{ $access->user?->paternal_surname }}
                                    {{ $access->user?->maternal_surname }}
                                </p>
                                <p class="text-xs text-gray-800 dark:text-gray-400">{{ $access->user?->email }}</p>
                            </div>
                        </div>
                        {{--                    <x-mary-badge--}}
                        {{--                        :value="ucfirst($access->permission_level ?? 'sin permisos')"--}}
                        {{--                        class="badge badge-outline"--}}
                        {{--                    />--}}
                    </div>
                @endforeach

                 @else
                    <p class="text-sm text-gray-600">No hay accesos de usuario asociados a este lead.</p>
                @endif

            @endif

        </div>
    </x-mary-card>

