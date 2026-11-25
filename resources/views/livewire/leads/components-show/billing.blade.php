    <x-mary-card class="shadow-xl">
        <x-slot:title>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-mary-icon name="o-document-text" class="h-5 w-5" />
                    <span>Información de Facturación</span>
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
                {{-- Razón Social --}}
                <div>
                    <x-mary-input
                            label="Razón Social"
                            wire:model="business_name"
                            icon="o-building-office-2"
                            class="input-primary"
                    />
                </div>

                {{-- RFC --}}
                <div>
                    <x-mary-input
                            label="RFC"
                            icon="o-document-text"
                            wire:model="rfc"
                            maxlength="20"
                            class="input-primary"
                    />
                </div>

                {{-- Email de Facturación --}}
                <div>
                    <x-mary-input
                            label="Email de Facturación"
                            icon="o-envelope"
                            type="email"
                            wire:model="billing_email"
                            class="input-primary"
                    />
                </div>

                {{-- Dirección --}}
                <div>
                    <x-mary-input
                            label="Estado"
                            icon="o-map"
                            wire:model="address_state"
                            class="input-primary"
                    />
                </div>

                <div>
                    <x-mary-input
                            label="Municipio"
                            icon="o-map-pin"
                            wire:model="municipality"
                            class="input-primary"
                    />
                </div>

                <div>
                    <x-mary-input
                            label="Colonia"
                            icon="o-home"
                            wire:model="colony"
                            class="input-primary"
                    />
                </div>

                <div>
                    <x-mary-input
                            label="Calle"
                            icon="o-arrow-long-right"
                            wire:model="street"
                            class="input-primary"
                    />
                </div>

                <div>
                    <x-mary-input
                            label="Número Exterior"
                            icon="o-hashtag"
                            wire:model="exterior_number"
                            maxlength="20"
                            class="input-primary"
                    />
                </div>

                {{-- Número Interior --}}
                <div>
                    <x-mary-input
                            label="Número Interior (Opcional)"
                            icon="o-hashtag"
                            wire:model="interior_number"
                            maxlength="20"
                            class="input-primary"
                    />
                </div>

                {{-- Código Postal --}}
                <div>
                    <x-mary-input
                            label="Código Postal"
                            icon="o-envelope-open"
                            wire:model="postal_code"
                            maxlength="5"
                            pattern="\d{5}"
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
                {{-- Razón Social --}}
                <div>
                    <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Razón Social</label>
                    @if($lead->billingInfo->business_name)
                        {{ $lead->billingInfo->business_name }}
                    @else
                        <span class="text-gray-800 dark:text-gray-400 italic">No especificada</span>
                    @endif
                </div>

                {{-- RFC --}}
                <div>
                    <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">RFC</label>
                    @if($lead->billingInfo->rfc)
                        {{ $lead->billingInfo->rfc }}
                    @else
                        <span class="text-gray-800 dark:text-gray-400 italic">No especificado</span>
                    @endif
                </div>

                {{-- Email de Facturación --}}
                <div>
                    <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Email de Facturación</label>
                    @if($lead->billingInfo->email)
                        {{ $lead->billingInfo->email }}
                    @else
                        <span class="text-gray-800 dark:text-gray-400 italic">No especificado</span>
                    @endif
                </div>

                {{-- Dirección --}}
                <div>
                    <label class="label text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Dirección</label>

                    @if($lead->billingInfo && $lead->billingInfo->address)
                        <div class="space-y-1 text-sm">
                            {{-- Calle y número --}}
                            <div>
                                @if($lead->billingInfo->address->street)
                                    {{ $lead->billingInfo->address->street }}
                                    @if($lead->billingInfo->address->exterior_number)
                                        {{ $lead->billingInfo->address->exterior_number }}
                                    @endif
                                    @if($lead->billingInfo->address->interior_number)
                                        -{{ $lead->billingInfo->address->interior_number }}
                                    @endif
                                @else
                                    <span class="text-gray-800 dark:text-gray-400 italic">No especificada</span>
                                @endif
                            </div>

                            {{-- Colonia --}}
                            <div class="text-gray-800 dark:text-gray-400">
                                @if($lead->billingInfo->address->colony)
                                    {{ $lead->billingInfo->address->colony }}
                                @else
                                    <span class="text-gray-800 dark:text-gray-400 italic">Colonia no especificada</span>
                                @endif
                            </div>

                            {{-- Municipio, Estado y Código Postal --}}
                            <div class="text-gray-800 dark:text-gray-400">
                                @if($lead->billingInfo->address->municipality || $lead->billingInfo->address->state || $lead->billingInfo->address->postal_code)
                                    @if($lead->billingInfo->address->municipality)
                                        {{ $lead->billingInfo->address->municipality }},
                                    @endif
                                    @if($lead->billingInfo->address->state)
                                        {{ $lead->billingInfo->address->state }}
                                    @endif
                                    @if($lead->billingInfo->address->postal_code)
                                        {{ $lead->billingInfo->address->postal_code }}
                                    @endif
                                @else
                                    <span class="text-gray-800 dark:text-gray-400 italic">Ubicación no especificada</span>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-sm text-gray-800 dark:text-gray-400 italic p-3 bg-gray-50 rounded-lg text-center">
                            <x-mary-icon name="o-map-pin" class="w-4 h-4 inline mr-1" />
                            No hay dirección registrada para este lead
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </x-mary-card>
