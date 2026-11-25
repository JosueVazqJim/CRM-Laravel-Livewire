<section class="w-full">
    <x-mary-header title="Fusionar leads" separator>
        <x-slot:subtitle>
            <span class="font-mono text-xs">Leads · {{ $leads->map(fn ($lead) => '#'.$lead->id)->implode(' ') }}</span>
            <p class="mt-1">Elige qué información conservar de cada lead. Los demás leads seleccionados serán eliminados al finalizar.</p>
        </x-slot:subtitle>
        <x-slot:actions>
            <x-mary-button label="Cancelar" icon="o-arrow-left" :link="route('leads.index')" class="btn-ghost" />
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 items-start gap-4 lg:grid-cols-[1fr_300px]">
        <div class="space-y-4">
            {{-- Lead sobreviviente --}}
            <x-mary-card title="Lead sobreviviente" subtitle="Conserva el registro y absorbe el resto.">
                <div class="grid gap-3" style="grid-template-columns: repeat({{ min($leads->count(), 4) }}, minmax(200px, 1fr))">
                    @foreach ($leads as $lead)
                        @php($survives = $survivorId === $lead->id)
                        <div>
                            <input type="radio" id="survivor-{{ $lead->id }}" class="sr-only" wire:model.live="survivorId" value="{{ $lead->id }}" />
                            <label for="survivor-{{ $lead->id }}"
                                   @class(['flex cursor-pointer flex-col gap-2 rounded-xl border px-4 py-3 transition-colors', 'border-primary bg-primary/10' => $survives, 'border-base-300 bg-base-100' => ! $survives])>
                                <div class="flex items-center gap-2">
                                    <span @class(['inline-block h-2.5 w-2.5 shrink-0 rounded-full border-2', 'border-primary bg-primary' => $survives, 'border-base-300' => ! $survives])></span>
                                    <span class="font-medium text-sm">{{ $lead->name_company ?: 'Sin nombre' }}</span>
                                </div>
                                <div class="flex items-center gap-2 pl-4">
                                    <span class="font-mono text-xs text-base-content/40">#{{ $lead->id }}</span>
                                    <span @class(['font-mono text-[10px] uppercase tracking-wider', 'text-primary' => $survives, 'text-base-content/40' => ! $survives])>
                                        {{ $survives ? 'sobrevive' : 'se elimina' }}
                                    </span>
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>
            </x-mary-card>

            {{-- Datos generales --}}
            <x-mary-card>
                <x-slot:title>
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-xs text-base-content/40">01</span>
                        <span>Datos generales</span>
                        <x-mary-badge :value="$this->conflictCount().' conflictos de '.count($this->comparableFields())" class="badge-ghost badge-sm font-mono" />
                    </div>
                </x-slot:title>

                <div class="overflow-x-auto">
                    <div class="min-w-[640px]">
                        <div class="grid items-center border-b border-base-200 pb-2" style="grid-template-columns: 190px repeat({{ $leads->count() }}, 1fr)">
                            <div></div>
                            @foreach ($leads as $lead)
                                <div class="flex items-center gap-2 px-2">
                                    <span @class(['h-1.5 w-1.5 rounded-full', 'bg-primary' => $survivorId === $lead->id, 'bg-base-300' => $survivorId !== $lead->id])></span>
                                    <span class="truncate text-xs font-medium">{{ $lead->name_company ?: 'Lead #'.$lead->id }}</span>
                                    <span class="font-mono text-[10px] text-base-content/40">#{{ $lead->id }}</span>
                                </div>
                            @endforeach
                        </div>

                        @foreach ($this->comparableFields() as $field => $label)
                            @php([$state, $stateColor] = $this->fieldRowState($field))
                            <div class="grid items-center border-b border-base-200/70 py-1" style="grid-template-columns: 190px repeat({{ $leads->count() }}, 1fr)">
                                <div class="flex flex-col gap-0.5 pr-3 py-1">
                                    <span class="text-xs font-medium">{{ $label }}</span>
                                    <span class="font-mono text-[10px] uppercase tracking-wider {{ $stateColor }}">{{ $state }}</span>
                                </div>
                                @foreach ($leads as $lead)
                                    @php($chosen = ($fieldChoices[$field] ?? null) === $lead->id)
                                    <div class="px-1 py-1">
                                        <input type="radio" id="field-{{ $field }}-{{ $lead->id }}" class="sr-only"
                                               wire:model.live="fieldChoices.{{ $field }}" value="{{ $lead->id }}" />
                                        <label for="field-{{ $field }}-{{ $lead->id }}"
                                               @class(['flex min-h-11 cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-xs transition-colors', 'border-primary bg-primary/10' => $chosen, 'border-base-300 bg-base-100' => ! $chosen])>
                                            <span class="break-words">{{ $this->displayValue($lead, $field) }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-mary-card>

            {{-- Facturación --}}
            <x-mary-card>
                <x-slot:title>
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-xs text-base-content/40">02</span>
                        <span>Facturación</span>
                    </div>
                </x-slot:title>
                <x-slot:subtitle>se conserva el par completo (datos fiscales + domicilio) de un solo lead</x-slot:subtitle>

                <div class="grid gap-3" style="grid-template-columns: repeat({{ min($leads->count(), 4) }}, minmax(200px, 1fr))">
                    @foreach ($leads as $lead)
                        @php($chosen = $billingLeadId === $lead->id)
                        <div>
                            <input type="radio" id="billing-{{ $lead->id }}" class="sr-only" wire:model.live="billingLeadId" value="{{ $lead->id }}" />
                            <label for="billing-{{ $lead->id }}"
                                   @class(['flex cursor-pointer flex-col gap-2 rounded-xl border px-4 py-3 transition-colors', 'border-primary bg-primary/10' => $chosen, 'border-base-300 bg-base-100' => ! $chosen])>
                                <div class="flex items-center gap-2">
                                    <span @class(['h-2 w-2 rounded-full', 'bg-primary' => $chosen, 'bg-base-300' => ! $chosen])></span>
                                    <span class="text-sm font-medium">{{ $lead->name_company ?: 'Lead #'.$lead->id }}</span>
                                </div>
                                <div class="flex flex-col gap-0.5 pl-4 font-mono text-[11px] text-base-content/50">
                                    <span>RFC · {{ $lead->billingInfo->rfc ?? '—' }}</span>
                                    <span>{{ $lead->billingInfo->address->street ?? 'Sin domicilio' }}</span>
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>
            </x-mary-card>

            {{-- Contactos --}}
            <x-mary-card>
                <x-slot:title>
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-xs text-base-content/40">03</span>
                        <span>Contactos</span>
                    </div>
                </x-slot:title>
                <x-slot:subtitle>marca qué personas se conservan (pueden ser de distintos leads)</x-slot:subtitle>

                @if ($totalContactPeople === 0)
                    <p class="text-sm text-base-content/50">Ninguno de los leads seleccionados tiene contactos registrados.</p>
                @else
                    <div class="space-y-4">
                        @foreach ($leads as $lead)
                            @continue($lead->contactPeople->isEmpty())
                            <div class="grid items-center gap-3" style="grid-template-columns: 190px 1fr">
                                <div class="flex items-center gap-2">
                                    <span @class(['h-1.5 w-1.5 rounded-full', 'bg-primary' => $survivorId === $lead->id, 'bg-base-300' => $survivorId !== $lead->id])></span>
                                    <span class="text-xs text-base-content/60">{{ $lead->name_company ?: 'Lead #'.$lead->id }}</span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($lead->contactPeople as $contact)
                                        @php($kept = in_array($contact->id, $contactPeopleIdsToKeep))
                                        <div>
                                            <input type="checkbox" id="contact-{{ $contact->id }}" class="sr-only"
                                                   wire:model.live="contactPeopleIdsToKeep" value="{{ $contact->id }}" />
                                            <label for="contact-{{ $contact->id }}"
                                                   @class(['flex cursor-pointer items-center gap-3 rounded-lg border px-3 py-2 text-sm transition-colors', 'border-primary bg-primary/10' => $kept, 'border-base-300 bg-base-100' => ! $kept])>
                                                <span @class(['flex h-4 w-4 shrink-0 items-center justify-center rounded border text-[10px] text-primary-content', 'border-primary bg-primary' => $kept, 'border-base-300' => ! $kept])>
                                                    @if ($kept) ✓ @endif
                                                </span>
                                                <span class="font-medium">{{ $contact->name_contact }}</span>
                                                @if ($contact->channels->isNotEmpty())
                                                    <span class="font-mono text-[11px] text-base-content/40">
                                                        {{ $contact->channels->map(fn ($c) => $c->type.': '.$c->channel)->implode(' · ') }}
                                                    </span>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-mary-card>
        </div>

        {{-- Resumen y acciones --}}
        <div class="lg:sticky lg:top-6">
            <x-mary-card title="Resumen">
                <div class="space-y-3">
                    <div class="flex flex-col gap-0.5">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-base-content/40">Sobreviviente</span>
                        <span class="text-sm font-medium">
                            {{ ($leads->firstWhere('id', $survivorId)?->name_company ?: 'Lead') }} #{{ $survivorId }}
                        </span>
                    </div>

                    <hr class="border-base-200" />

                    <div class="space-y-1.5">
                        @foreach ($this->tallies() as $t)
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 truncate">
                                    <span @class(['h-1.5 w-1.5 shrink-0 rounded-full', 'bg-primary' => $t['survives'], 'bg-base-300' => ! $t['survives']])></span>
                                    <span class="truncate text-xs text-base-content/60">{{ $t['lead']->name_company ?: 'Lead #'.$t['lead']->id }}</span>
                                </div>
                                <span class="font-mono text-xs">{{ $t['count'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <hr class="border-base-200" />

                    <ul class="space-y-1 text-xs text-base-content/60">
                        <li>{{ $totalQuotes }} cotizaciones se consolidan</li>
                        <li>{{ $totalActivities }} actividades</li>
                        <li>{{ $totalNotes }} notas</li>
                        <li>{{ $totalUserAccess }} accesos compartidos</li>
                        <li>{{ count($contactPeopleIdsToKeep) }} contactos conservados</li>
                    </ul>

                    <x-mary-alert icon="o-exclamation-triangle" class="alert-warning text-xs">
                        Los leads que no sean el sobreviviente se eliminarán permanentemente. Esta acción no se puede deshacer.
                    </x-mary-alert>

                    <div class="flex flex-col gap-2 pt-1">
                        <x-mary-button label="Fusionar leads" icon="o-arrows-pointing-in" wire:click="confirmMerge" class="btn-primary" />
                        <x-mary-button label="Cancelar" :link="route('leads.index')" class="btn-ghost" />
                    </div>
                </div>
            </x-mary-card>
        </div>
    </div>

    <x-mary-modal wire:model="confirming" title="Confirmar fusión">
        <p>Vas a fusionar {{ count($leadIds) }} leads en uno solo. Los leads que no sean el sobreviviente se eliminarán permanentemente junto con los contactos no conservados.</p>
        <p class="mt-2 font-medium">¿Confirmas continuar?</p>

        <x-slot:actions>
            <x-mary-button label="Cancelar" wire:click="cancelConfirm" class="btn-ghost" />
            <x-mary-button label="Confirmar fusión" wire:click="merge" class="btn-error" spinner="merge" />
        </x-slot:actions>
    </x-mary-modal>
</section>
