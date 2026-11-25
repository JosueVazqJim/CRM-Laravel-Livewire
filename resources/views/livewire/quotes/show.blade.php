<div class="space-y-6">

    {{-- Header --}}
    <x-mary-header separator>
        <x-slot:title>
            <div class="flex items-center gap-3">
                <span>Cotización {{ $quoteForm->folio }}</span>
                <div class="badge {{ $statusBadge['class'] }} badge-lg gap-1">
                    <x-mary-icon name="{{ $statusBadge['icon'] }}" class="w-4 h-4" />
                    {{ $statusBadge['label'] }}
                </div>
            </div>
        </x-slot:title>

        <x-slot:subtitle >
            Creada el {{ $quoteForm->timestamps['created_at']->format('d/m/Y H:i') }}
        </x-slot:subtitle>
        <x-slot:actions>
            {{--                <x-mary-button label="Actualizar" icon="o-pencil" :link="route('leads.update', $lead)" class="btn-primary" />--}}
            <div class="flex flex-wrap items-end gap-2">
            <x-mary-select
                label="Estado"
                wire:model.live="selectedStatus"
                :options="$statusOptions"
                class="w-44"
            />
            <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-4 gap-2">
                <x-mary-button wire:click="$set('previewOpen', true)" class="btn btn-outline btn-sm">
                    <x-mary-icon name="o-eye" class="w-4 h-4" />
                    Vista Previa
                </x-mary-button>
                <x-mary-button
                        wire:click="startRevision"
                        class="btn btn-outline btn-sm"
                        spinner="startRevision"
                >
                    <x-mary-icon name="o-pencil-square" class="w-4 h-4" />
                    Editar
                </x-mary-button>
                <x-mary-button
                        wire:click="startRecotizacion"
                        class="btn btn-primary btn-sm"
                        spinner="startRecotizacion"
                >
                    <x-mary-icon name="o-arrow-path" class="w-4 h-4" />
                    Recotizar
                </x-mary-button>
                <x-mary-button label="Volver" icon="o-arrow-left" :link="route('leads.show', $quoteVersion->quote->lead)" class="btn-ghost" />

            </div>
            </div>
        </x-slot:actions>
    </x-mary-header>

    {{-- Modal de vista previa --}}
    @php
        $previewLead    = $quoteVersion->quote->lead;
        $previewContact = $previewLead->contactPeople->first();
        $previewChannels = $previewContact?->channels ?? collect();
        $previewPhone   = $previewChannels->first(fn($c) => in_array(strtolower($c->type), ['phone', 'telefono', 'teléfono']))?->channel ?? '';
        $previewEmail   = $previewChannels->first(fn($c) => strtolower($c->type) === 'email')?->channel ?? '';
        $previewBa      = $previewLead->billingInfo?->address;
        $previewAddress = $previewBa ? implode(', ', array_filter([
            trim(($previewBa->street ?? '') . ' ' . ($previewBa->exterior_number ?? '') .
                ($previewBa->interior_number ? ' Int. ' . $previewBa->interior_number : '')),
            $previewBa->colony ?? null,
            $previewBa->municipality ?? null,
            $previewBa->state ?? null,
            $previewBa->postal_code ? 'C.P. ' . $previewBa->postal_code : null,
        ])) : '';
    @endphp
    <x-quotes.show.preview-modal
            :previewOpen="$previewOpen"
            :previewUrl="$this->previewUrl"
            :pdfUrl="$this->pdfUrl"
            :hasMxn="$this->hasMxnRate"
            :folio="$quoteVersion->folio"
            :companyName="$quoteVersion->quote->company->name"
            :initClientName="$previewContact?->name_contact ?? $previewLead->name_company"
            :initClientCompany="$previewLead->name_company"
            :initClientPhone="$previewPhone"
            :initClientEmail="$previewEmail"
            :initClientAddress="$previewAddress"
    />

    {{-- Modal de Orden de Compra --}}
    <x-quotes.show.purchase-order-modal
            :purchaseOrderModalOpen="$purchaseOrderModalOpen"
            :poTotalMxn="$poTotalMxn"
            :poAdvanceAmount="$poAdvanceAmount"
    />

    <div class="space-y-6">
        <x-mary-card class="shadow-xl bg-base-200">
            <div class="w-full overflow-x-auto -mx-1 px-1">
                <x-mary-tabs wire:model="tab" class="tabs-boxed flex-nowrap min-w-max pb-2">

                    {{-- Tab: Detalles de Cotización --}}
                    <x-mary-tab name="details" label="Detalles de Cotización">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            {{-- Columna izquierda - Información general --}}
                            <div class="lg:col-span-2 space-y-6">
                                <x-quotes.show.info-general
                                        :lead="$quoteVersion->quote->lead"
                                        :company="$quoteVersion->quote->company"
                                        :quoteForm="$quoteForm"
                                        :statusBadge="$statusBadge"
                                />

                                <x-quotes.show.purchase-order-info
                                        :quoteVersion="$quoteVersion"
                                        :purchaseOrder="$purchaseOrder"
                                        :totalMxn="$poTotalMxn"
                                />
                            </div>

                            {{-- Columna derecha - Totales --}}
                            <div class="lg:col-span-1">
                                <x-quotes.show.totals-summary
                                        :quoteForm="$quoteForm"
                                        :currencySummary="$currencySummary"
                                />
                            </div>
                        </div>
                    </x-mary-tab>

                    {{-- Tab: Productos y Conceptos --}}
                    <x-mary-tab name="items">
                        <x-slot:label>
                            Productos y Conceptos
                            <x-mary-badge value="{{ count($quoteForm->items) + count($concepts) }}" class="badge-primary badge-sm" />
                        </x-slot:label>
                        <x-quotes.show.items-combined
                            :quoteForm="$quoteForm"
                            :concepts="$concepts"
                        />
                    </x-mary-tab>

                    {{-- Tab: Tasas de cambio --}}
                    <x-mary-tab name="exchange_rates" label="Tasas de cambio utilizadas">
                        <x-quotes.show.exchange-rates-table :exchangeRates="$quoteForm->exchangeRatesSnapshot" />
                    </x-mary-tab>

                    {{-- Tab: Notas --}}
                    <x-mary-tab name="notes">
                        <x-slot:label>
                            Notas
                            <x-mary-badge value="{{ count($quoteForm->notes) }}" class="badge-primary badge-sm" />
                        </x-slot:label>
                        <x-quotes.show.notes-list :notes="$quoteForm->notes" />
                    </x-mary-tab>

                    {{-- Tab: Versiones --}}
                    <x-mary-tab name="versions">
                        <x-slot:label>
                            Versiones
                            <x-mary-badge value="{{ $childVersions->count() }}" class="badge-primary badge-sm" />
                        </x-slot:label>
                        <x-quotes.show.versions-tree
                                :parentVersion="$parentVersion"
                                :childVersions="$childVersions"
                                :currentVersion="$quoteVersion"
                        />
                    </x-mary-tab>

                </x-mary-tabs>
            </div>
        </x-mary-card>
    </div>
</div>