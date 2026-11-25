@props(['lead'])

@if($lead->billingInfo)
    <x-mary-card class="shadow-xl">
        <x-slot:title>
            <div class="flex items-center gap-2">
                <x-mary-icon name="o-document-text" class="h-5 w-5"></x-mary-icon>
                <span>Información de Facturación</span>
            </div>
        </x-slot:title>

        <div class="space-y-4">
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
        </div>
    </x-mary-card>
@endif

