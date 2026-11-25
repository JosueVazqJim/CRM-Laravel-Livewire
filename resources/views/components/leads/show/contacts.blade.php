@props(['lead'])

@if($lead->contactPeople->count() > 0)
    <x-mary-card class="shadow-xl">
        <x-slot:title>
            <div class="flex items-center gap-2">
                <x-mary-icon name="o-phone" class="h-5 w-5"></x-mary-icon>
                <span>Contactos del Lead</span>
                <x-mary-badge value="{{ $lead->contactPeople->count() }}" class="badge-primary badge-sm" />
            </div>
        </x-slot:title>

        <div class="space-y-4">
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
        </div>
    </x-mary-card>
@endif

