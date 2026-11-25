@props([
    'previewOpen',
    'previewUrl',
    'pdfUrl',
    'folio'             => 'cotizacion',
    'companyName'       => '',
    'initClientName'    => '',
    'initClientCompany' => '',
    'initClientPhone'   => '',
    'initClientEmail'   => '',
    'initClientAddress' => '',
    'hasMxn'             => true,
])

<div
        x-data="{
        showNotes: true,
        editClientData: false,
        showMercadoPago: false,
        currencyDisplay: 'both',
        downloading: false,
        clientName:    {{ Js::from($initClientName) }},
        clientCompany: {{ Js::from($initClientCompany) }},
        clientPhone:   {{ Js::from($initClientPhone) }},
        clientEmail:   {{ Js::from($initClientEmail) }},
        clientAddress: {{ Js::from($initClientAddress) }},
        showClientName:    true,
        showClientCompany: true,
        showClientPhone:   true,
        showClientEmail:   true,
        showClientAddress: true,
    }"
        x-init="
        const postAll = () => {
            const frame = document.getElementById('quote-preview-frame');
            if (!frame || !frame.contentWindow) return;
            frame.contentWindow.postMessage({
                showNotes: showNotes,
                showMercadoPago: showMercadoPago,
                clientData: {
                    name:        clientName,
                    company:     clientCompany,
                    phone:       clientPhone,
                    email:       clientEmail,
                    address:     clientAddress,
                    editing:     editClientData,
                    showName:    showClientName,
                    showCompany: showClientCompany,
                    showPhone:   showClientPhone,
                    showEmail:   showClientEmail,
                    showAddress: showClientAddress,
                }
            }, '*');
        };
        // La conversión de moneda afecta todos los precios renderizados en el
        // servidor (productos, conceptos, totales), así que recargamos el
        // iframe en vez de actualizarlo por postMessage.
        const reloadCurrency = () => {
            const frame = document.getElementById('quote-preview-frame');
            if (!frame) return;
            const url = new URL(frame.src, window.location.origin);
            url.searchParams.set('currency_display', currencyDisplay);
            frame.src = url.toString();
        };
        $watch('showNotes',           postAll);
        $watch('editClientData',      postAll);
        $watch('showMercadoPago',     postAll);
        $watch('currencyDisplay',     reloadCurrency);
        $watch('clientName',          postAll);
        $watch('clientCompany',       postAll);
        $watch('clientPhone',         postAll);
        $watch('clientEmail',         postAll);
        $watch('clientAddress',       postAll);
        $watch('showClientName',      postAll);
        $watch('showClientCompany',   postAll);
        $watch('showClientPhone',     postAll);
        $watch('showClientEmail',     postAll);
        $watch('showClientAddress',   postAll);
    "
>
    <x-mary-modal wire:model="previewOpen" title="Vista previa de cotización" box-class="w-11/12 max-w-5xl">
        <div class="space-y-6">
            <!-- Toggles Section -->
            <div class="flex flex-wrap gap-4 p-4 shadow-xl bg-base-200 rounded-xl">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <div class="relative">
                        <input type="checkbox" class="sr-only" x-model="showNotes">
                        <div class="w-10 h-5 bg-gray-300 rounded-full shadow-inner transition-all duration-300 group-hover:bg-gray-400"
                             :class="showNotes ? 'bg-primary group-hover:bg-primary-focus' : ''"></div>
                        <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow transition-all duration-300"
                             :class="showNotes ? 'translate-x-5' : ''"></div>
                    </div>
                    <span class="text-sm font-medium text-base-content">Mostrar notas</span>
                </label>

                <label class="flex items-center gap-3 cursor-pointer group">
                    <div class="relative">
                        <input type="checkbox" class="sr-only" x-model="editClientData">
                        <div class="w-10 h-5 bg-gray-300 rounded-full shadow-inner transition-all duration-300 group-hover:bg-gray-400"
                             :class="editClientData ? 'bg-primary group-hover:bg-primary-focus' : ''"></div>
                        <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow transition-all duration-300"
                             :class="editClientData ? 'translate-x-5' : ''"></div>
                    </div>
                    <span class="text-sm font-medium text-base-content">Editar datos del cliente</span>
                </label>

                <label class="flex items-center gap-3 cursor-pointer group">
                    <div class="relative">
                        <input type="checkbox" class="sr-only" x-model="showMercadoPago">
                        <div class="w-10 h-5 bg-gray-300 rounded-full shadow-inner transition-all duration-300 group-hover:bg-gray-400"
                             :class="showMercadoPago ? 'bg-primary group-hover:bg-primary-focus' : ''"></div>
                        <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow transition-all duration-300"
                             :class="showMercadoPago ? 'translate-x-5' : ''"></div>
                    </div>
                    <span class="text-sm font-medium text-base-content">Opciones Mercado Pago</span>
                </label>

                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-base-content">Monedas a mostrar:</span>
                    <div class="join">
                        <input type="radio" name="currencyDisplay" value="both" x-model="currencyDisplay"
                               aria-label="USD y MXN" class="join-item btn btn-sm" />
                        <input type="radio" name="currencyDisplay" value="usd" x-model="currencyDisplay"
                               aria-label="Solo USD" class="join-item btn btn-sm" />
                        @if($hasMxn)
                            <input type="radio" name="currencyDisplay" value="mxn" x-model="currencyDisplay"
                                   aria-label="Solo MXN" class="join-item btn btn-sm" />
                        @endif
                    </div>
                </div>
            </div>

            <!-- Client Data Section -->
            <div x-show="editClientData" x-cloak class="bg-base-100 rounded-xl border border-base-300 overflow-hidden shadow-xl">
                <div class="bg-base-200 px-5 py-3 border-b border-base-300">
                    <h3 class="text-sm font-semibold text-base-content flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Datos del Cliente
                    </h3>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Nombre -->
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" class="sr-only" x-model="showClientName">
                                    <div class="w-8 h-4 bg-gray-300 rounded-full transition-all duration-300"
                                         :class="showClientName ? 'bg-primary' : ''"></div>
                                    <div class="absolute left-0.5 top-0.5 w-3 h-3 bg-white rounded-full transition-all duration-300"
                                         :class="showClientName ? 'translate-x-4' : ''"></div>
                                </div>
                                <span class="text-xs font-medium text-base-content/70">Mostrar</span>
                            </label>
                            <input
                                    type="text"
                                    x-model="clientName"
                                    :disabled="!showClientName"
                                    class="w-full px-3 py-2 text-sm border border-base-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all duration-200 bg-base-100 text-base-content"
                                    :class="!showClientName ? 'opacity-50 cursor-not-allowed bg-base-200' : 'hover:border-base-400'"
                                    placeholder="Nombre completo"
                            >
                            <label class="text-xs text-base-content/60 font-medium block -mt-1">Nombre</label>
                        </div>

                        <!-- Empresa -->
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" class="sr-only" x-model="showClientCompany">
                                    <div class="w-8 h-4 bg-gray-300 rounded-full transition-all duration-300"
                                         :class="showClientCompany ? 'bg-primary' : ''"></div>
                                    <div class="absolute left-0.5 top-0.5 w-3 h-3 bg-white rounded-full transition-all duration-300"
                                         :class="showClientCompany ? 'translate-x-4' : ''"></div>
                                </div>
                                <span class="text-xs font-medium text-base-content/70">Mostrar</span>
                            </label>
                            <input
                                    type="text"
                                    x-model="clientCompany"
                                    :disabled="!showClientCompany"
                                    class="w-full px-3 py-2 text-sm border border-base-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all duration-200 bg-base-100 text-base-content"
                                    :class="!showClientCompany ? 'opacity-50 cursor-not-allowed bg-base-200' : 'hover:border-base-400'"
                                    placeholder="Nombre de la empresa"
                            >
                            <label class="text-xs text-base-content/60 font-medium block -mt-1">Empresa</label>
                        </div>

                        <!-- Teléfono -->
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" class="sr-only" x-model="showClientPhone">
                                    <div class="w-8 h-4 bg-gray-300 rounded-full transition-all duration-300"
                                         :class="showClientPhone ? 'bg-primary' : ''"></div>
                                    <div class="absolute left-0.5 top-0.5 w-3 h-3 bg-white rounded-full transition-all duration-300"
                                         :class="showClientPhone ? 'translate-x-4' : ''"></div>
                                </div>
                                <span class="text-xs font-medium text-base-content/70">Mostrar</span>
                            </label>
                            <input
                                    type="text"
                                    x-model="clientPhone"
                                    :disabled="!showClientPhone"
                                    class="w-full px-3 py-2 text-sm border border-base-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all duration-200 bg-base-100 text-base-content"
                                    :class="!showClientPhone ? 'opacity-50 cursor-not-allowed bg-base-200' : 'hover:border-base-400'"
                                    placeholder="Teléfono de contacto"
                            >
                            <label class="text-xs text-base-content/60 font-medium block -mt-1">Teléfono</label>
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" class="sr-only" x-model="showClientEmail">
                                    <div class="w-8 h-4 bg-gray-300 rounded-full transition-all duration-300"
                                         :class="showClientEmail ? 'bg-primary' : ''"></div>
                                    <div class="absolute left-0.5 top-0.5 w-3 h-3 bg-white rounded-full transition-all duration-300"
                                         :class="showClientEmail ? 'translate-x-4' : ''"></div>
                                </div>
                                <span class="text-xs font-medium text-base-content/70">Mostrar</span>
                            </label>
                            <input
                                    type="email"
                                    x-model="clientEmail"
                                    :disabled="!showClientEmail"
                                    class="w-full px-3 py-2 text-sm border border-base-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all duration-200 bg-base-100 text-base-content"
                                    :class="!showClientEmail ? 'opacity-50 cursor-not-allowed bg-base-200' : 'hover:border-base-400'"
                                    placeholder="correo@ejemplo.com"
                            >
                            <label class="text-xs text-base-content/60 font-medium block -mt-1">Email</label>
                        </div>

                        <!-- Dirección -->
                        <div class="md:col-span-2 space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" class="sr-only" x-model="showClientAddress">
                                    <div class="w-8 h-4 bg-gray-300 rounded-full transition-all duration-300"
                                         :class="showClientAddress ? 'bg-primary' : ''"></div>
                                    <div class="absolute left-0.5 top-0.5 w-3 h-3 bg-white rounded-full transition-all duration-300"
                                         :class="showClientAddress ? 'translate-x-4' : ''"></div>
                                </div>
                                <span class="text-xs font-medium text-base-content/70">Mostrar</span>
                            </label>
                            <textarea
                                    x-model="clientAddress"
                                    :disabled="!showClientAddress"
                                    class="w-full px-3 py-2 text-sm border border-base-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all duration-200 resize-none bg-base-100 text-base-content"
                                    :class="!showClientAddress ? 'opacity-50 cursor-not-allowed bg-base-200' : 'hover:border-base-400'"
                                    rows="2"
                                    placeholder="Calle, número, colonia, ciudad..."
                            ></textarea>
                            <label class="text-xs text-base-content/60 font-medium block -mt-1">Dirección</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Iframe -->
            <iframe
                    id="quote-preview-frame"
                    src="{{ $previewUrl }}"
                    class="w-full border border-base-300 rounded-xl shadow-xl"
                    style="height: 65vh;"
                    @load="$el.contentWindow.postMessage({
                    showNotes: showNotes,
                    showMercadoPago: showMercadoPago,
                    clientData: {
                        name:        clientName,
                        company:     clientCompany,
                        phone:       clientPhone,
                        email:       clientEmail,
                        address:     clientAddress,
                        editing:     editClientData,
                        showName:    showClientName,
                        showCompany: showClientCompany,
                        showPhone:   showClientPhone,
                        showEmail:   showClientEmail,
                        showAddress: showClientAddress,
                    }
                }, '*')"
            ></iframe>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cerrar" wire:click="$set('previewOpen', false)" class="btn-outline" />
            <button
                class="btn btn-primary gap-2"
                :disabled="downloading"
                @click="
                    const url = `{{ $pdfUrl }}?notes=` + showNotes +
                        `&mercadopago=`    + showMercadoPago +
                        `&currency_display=` + currencyDisplay +
                        `&client_name=`    + encodeURIComponent(clientName) +
                        `&client_company=` + encodeURIComponent(clientCompany) +
                        `&client_phone=`   + encodeURIComponent(clientPhone) +
                        `&client_email=`   + encodeURIComponent(clientEmail) +
                        `&client_address=` + encodeURIComponent(clientAddress) +
                        `&show_name=`      + showClientName +
                        `&show_company=`   + showClientCompany +
                        `&show_phone=`     + showClientPhone +
                        `&show_email=`     + showClientEmail +
                        `&show_address=`   + showClientAddress;

                    const filename = '{{ $companyName }}-{{ $folio }}.pdf';

                    if (window.showSaveFilePicker) {
                        window.showSaveFilePicker({
                            suggestedName: filename,
                            types: [{ description: 'PDF', accept: { 'application/pdf': ['.pdf'] } }],
                        }).then(async handle => {
                            downloading = true;
                            const response = await fetch(url);
                            const blob = await response.blob();
                            const writable = await handle.createWritable();
                            await writable.write(blob);
                            await writable.close();
                            downloading = false;
                        }).catch(e => {
                            if (e.name !== 'AbortError') {
                                downloading = true;
                                fetch(url).then(r => r.blob()).then(blob => {
                                    const a = document.createElement('a');
                                    a.href = URL.createObjectURL(blob);
                                    a.download = filename;
                                    a.click();
                                    URL.revokeObjectURL(a.href);
                                    downloading = false;
                                }).catch(() => { downloading = false; });
                            }
                        });
                    } else {
                        downloading = true;
                        fetch(url).then(r => r.blob()).then(blob => {
                            const a = document.createElement('a');
                            a.href = URL.createObjectURL(blob);
                            a.download = filename;
                            a.click();
                            URL.revokeObjectURL(a.href);
                            downloading = false;
                        }).catch(() => { downloading = false; });
                    }
                "
            >
                <x-mary-icon name="o-arrow-down-tray"></x-mary-icon>
                <span x-text="downloading ? 'Descargando...' : 'Descargar PDF'"></span>
            </button>
        </x-slot:actions>
    </x-mary-modal>
</div>