    <x-mary-card class="shadow-xl">
        <x-slot:title>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-mary-icon name="o-phone" class="h-5 w-5" />
                    <span>Contactos del Lead</span>
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

        <div class="">
            @if($editing)
                {{-- Contactos dinámicos --}}
                <div class="space-y-4">
                    @forelse($contacts as $contactIndex => $contact)
                        <div class="card bg-base-100 shadow-md border border-base-200">
                            <div class="card-body">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="card-title text-lg flex items-center gap-2">
                                        <x-mary-icon name="o-user-group" class="w-5 h-5" />
                                        Contacto {{ $contactIndex + 1 }}
                                    </h3>
                                    @if(count($contacts) > 1)
                                        <x-mary-button
                                                icon="o-trash"
                                                wire:click="removeContact({{ $contactIndex }})"
                                                class="btn-sm btn-error"
                                                label="Eliminar"
                                        />
                                    @endif
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    {{-- Nombre del Contacto --}}
                                    <div>
                                        <x-mary-input
                                                label="Nombre del Contacto"
                                                icon="o-user"
                                                wire:model="contacts.{{ $contactIndex }}.name_contact"
                                                placeholder="Ej. Juan Pérez"
                                                :error="$errors->first('contacts.' . $contactIndex . '.name_contact')"
                                                class="input-primary"
                                                required
                                        />
                                    </div>

                                    {{-- Cliente Recurrente --}}
                                    <div class="form-control flex justify-center pt-2">
                                        <label class="cursor-pointer label gap-2">
                                            <x-mary-checkbox
                                                    wire:model="contacts.{{ $contactIndex }}.recurring_client"
                                            />
                                            <span class="label-text">Cliente Recurrente</span>
                                        </label>
                                        @error('contacts.' . $contactIndex . '.recurring_client')
                                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Canales de Contacto (dinámicos) --}}
                                <div class="divider divider-sm">Canales de Contacto</div>

                                <div class="space-y-3">
                                    @forelse($contact['channels'] ?? [] as $channelIndex => $channel)
                                        <div class="flex gap-3 items-end">
                                            {{-- Tipo de Canal --}}
                                            <div class="flex-1">
                                                <x-mary-select
                                                        label="Tipo"
                                                        wire:model="contacts.{{ $contactIndex }}.channels.{{ $channelIndex }}.type"
                                                        :options="[
                                                            ['id' => 'teléfono', 'name' => 'Teléfono'],
                                                            ['id' => 'email', 'name' => 'Email'],
                                                            ['id' => 'whatsapp', 'name' => 'WhatsApp'],
                                                            ['id' => 'linkedin', 'name' => 'LinkedIn'],
                                                            ['id' => 'red social', 'name' => 'Red social'],
                                                            ['id' => 'otro', 'name' => 'Otro'],
                                                        ]"
                                                        :error="$errors->first('contacts.' . $contactIndex . '.channels.' . $channelIndex . '.type')"
                                                        class="input-primary"
                                                        required
                                                        placeholder="Selecciona el tipo de canal"
                                                />
                                            </div>

                                            {{-- Valor del Canal --}}
                                            <div class="flex-1">
                                                <x-mary-input
                                                        label="Valor"
                                                        wire:model="contacts.{{ $contactIndex }}.channels.{{ $channelIndex }}.channel"
                                                        placeholder="Ej. +34 123 456 789"
                                                        :error="$errors->first('contacts.' . $contactIndex . '.channels.' . $channelIndex . '.channel')"
                                                        class="input-primary"
                                                        required
                                                />
                                            </div>

                                            {{-- Botón eliminar canal --}}
                                            @if(count($contact['channels'] ?? []) > 1)
                                                <x-mary-button
                                                        icon="o-x-mark"
                                                        wire:click="removeChannelFromContact({{ $contactIndex }}, {{ $channelIndex }})"
                                                        class="btn-sm btn-error btn-outline"
                                                />
                                            @endif
                                        </div>
                                    @empty
                                        <p class="text-sm text-gray-500">Sin canales asignados</p>
                                    @endforelse

                                    {{-- Botón para añadir canal --}}
                                    <div class="pt-2">
                                        <x-mary-button
                                                label="Añadir Canal"
                                                icon="o-plus"
                                                wire:click="addChannelToContact({{ $contactIndex }})"
                                                class="btn-sm btn-primary btn-outline"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-info">
                            <x-mary-icon name="o-information-circle" class="w-5 h-5" />
                            <span>No hay contactos creados. Haz clic en "Añadir Contacto" para crear uno.</span>
                        </div>
                    @endforelse
                </div>

                {{-- Botón para añadir nuevo contacto --}}
                <div class="flex justify-center pt-4">
                    <x-mary-button
                            label="Añadir Contacto"
                            icon="o-plus-circle"
                            wire:click="addContact"
                            class="btn-primary"
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
                @if($lead->contactPeople->count() > 0)

                @foreach($lead->contactPeople as $contact)
                    <div class="border-l-4 border-primary pl-4 py-2">
                        {{-- Nombre del Contacto --}}
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold">{{ $contact->name_contact }}</h4>
                            <x-mary-badge
                                    :value="$contact->recurring_client ? 'Recurrente' : 'Ocasional'"
                                    class="badge {{ $contact->recurring_client ? 'badge-success' : 'badge-info' }}"
                            />
                        </div>

                        {{-- Canales de Contacto --}}
                        @if($contact->channels->count() > 0)
                            <div class="space-y-1">
                                @foreach($contact->channels as $channel)
                                    <div class="flex items-center gap-2 text-sm">
                                        @php
                                            $icon = match($channel->type) {
                                                'email' => 'o-envelope',
                                                'phone' => 'o-phone',
                                                'whatsapp' => 'o-chat-bubble-left',
                                                'linkedin' => 'o-link',
                                                default => 'o-globe-alt'
                                            };
                                        @endphp
                                        <x-mary-icon :name="$icon" class="h-4 w-4 text-primary"></x-mary-icon>
                                        <span class="font-medium capitalize">{{ $channel->type }}:</span>
                                        <span class="text-gray-800 dark:text-gray-400">{{ $channel->channel }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-800 dark:text-gray-400">No hay canales registrados</p>
                        @endif
                    </div>
                @endforeach
                @endif

            @endif
        </div>
    </x-mary-card>

