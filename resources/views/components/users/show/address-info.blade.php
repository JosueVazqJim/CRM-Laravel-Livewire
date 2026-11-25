@props(['user', 'stateName', 'municipalityName'])

<x-mary-card class="shadow-xl">
    <x-slot:title>
        <div class="flex items-center gap-2">
            <x-mary-icon name="o-map-pin" class="w-5 h-5" />
            <span>Dirección</span>
        </div>
    </x-slot:title>

    <x-slot:subtitle>
        Dirección registrada del usuario
    </x-slot:subtitle>

    @if($user->address)
        @php $address = $user->address; @endphp
        <div class="grid grid-cols-1 gap-4">
            <div class="border border-base-300 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    {{-- Icono con badge de tipo --}}
                    <div class="relative">
                        <div class="bg-primary/10 p-3 rounded-full">
                            <x-mary-icon name="o-map" class="w-5 h-5 text-primary" />
                        </div>
                    </div>

                    {{-- Información de dirección --}}
                    <div class="flex-1 space-y-2">
                        {{-- Tipo de dirección --}}
                        @if($address->type)
                            <div class="flex items-center gap-2">
                                <span class="badge badge-primary badge-outline">{{ $address->type }}</span>
                            </div>
                        @endif

                        {{-- Calle y números --}}
                        <div class="text-lg font-medium">
                            {{ $address->street }}
                            <span class="">
                                #{{ $address->exterior_number ?? 'S/N' }}
                                @if($address->interior_number)
                                    <span class="text-sm">- Int {{ $address->interior_number }}</span>
                                @endif
                            </span>
                        </div>

                        {{-- Colonia --}}
                        @if($address->colony)
                            <div class="flex items-center gap-1 ">
                                <span>{{ $address->colony }}</span>
                            </div>
                        @endif

                        {{-- Municipio y Estado (con nombres reales) --}}
                        <div class="flex items-center gap-1 ">
                            <span>
                                @if($municipalityName)
                                    {{ $municipalityName }},
                                @else
                                    {{ $address->municipality }},
                                @endif

                                @if($stateName)
                                    {{ $stateName }}
                                @else
                                    {{ $address->state }}
                                @endif
                            </span>
                        </div>

                        {{-- Código Postal --}}
                        @if($address->postal_code)
                            <div class="flex items-center gap-1 ">
                                <span>CP {{ $address->postal_code }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-8 ">
            <p>No hay direcciones registradas</p>
        </div>
    @endif
</x-mary-card>