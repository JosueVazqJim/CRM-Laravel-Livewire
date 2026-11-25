@props(['business_name' => '', 'rfc' => '', 'billing_email' => '', 'address_state' => '', 'municipality' => '', 'colony' => '', 'street' => '', 'exterior_number' => '', 'interior_number' => '', 'postal_code' => '', 'tabErrors' => []])
{{--@props(['business_name' => '', 'rfc' => '', 'billing_email' => '', 'address_state' => '', 'municipality' => '', 'colony' => '', 'street' => '', 'exterior_number' => '', 'interior_number' => '', 'postal_code' => '', 'states' => [], 'municipalities' => [], 'loadingMunicipalities' => false, 'tabErrors' => []])--}}

<x-slot:label>
    <div class="flex items-center gap-1 whitespace-nowrap">
        @if($tabErrors['billing'] ?? false)
            <x-mary-badge value="{{ $tabErrors['billing'] }}" class="badge-error badge-sm"/>
        @endif
    </div>
</x-slot:label>

<div class="space-y-6">
    {{-- INFORMACIÓN DE FACTURACIÓN --}}
    <div class="divider divider-neutral">Información de Facturación</div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Razón Social --}}
        <div>
            <x-mary-input
                label="Razón Social"
                icon="o-building-office-2"
                wire:model="business_name"
                placeholder="Ej. Acme S.A. de C.V."
                :error="$errors->first('business_name')"
                class="input-primary"
            />
        </div>

        {{-- RFC --}}
        <div>
            <x-mary-input
                label="RFC"
                icon="o-document-text"
                wire:model="rfc"
                placeholder="Ej. ACM123456ABC"
                maxlength="20"
                :error="$errors->first('rfc')"
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
                placeholder="factura@empresa.com"
                :error="$errors->first('billing_email')"
                class="input-primary"
            />
        </div>
    </div>

    {{-- DOMICILIO DE FACTURACIÓN --}}
    <div class="divider divider-neutral">Domicilio de Facturación</div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Estado --}}
        <div>
{{--            <x-mary-select--}}
{{--                label="Estado"--}}
{{--                icon="o-map"--}}
{{--                wire:model="address_state"--}}
{{--                :options="collect($states)->map(fn($s) => ['id' => $s['id'], 'name' => $s['name']])->toArray()"--}}
{{--                :error="$errors->first('address_state')"--}}
{{--            />--}}
            <x-mary-input
                    label="Estado"
                    icon="o-map"
                    wire:model="address_state"
                    placeholder="Ej. Puebla"
                    :error="$errors->first('address_state')"
                    class="input-primary"
            />
        </div>

        {{-- Municipio --}}
        <div>
{{--            <x-mary-select--}}
{{--                label="Municipio"--}}
{{--                icon="o-map-pin"--}}
{{--                wire:model="municipality"--}}
{{--                :options="collect($municipalities)->map(fn($m) => ['id' => $m['name'], 'name' => $m['name']])->toArray()"--}}
{{--                :disabled="$loadingMunicipalities || empty($address_state)"--}}
{{--                :error="$errors->first('municipality')"--}}
{{--            />--}}
{{--            --}}
{{--            @if($loadingMunicipalities)--}}
{{--                <span class="text-sm text-gray-500">Cargando municipios...</span>--}}
{{--            @endif--}}

            <x-mary-input
                    label="Municipio"
                    icon="o-map-pin"
                    wire:model="municipality"
                    placeholder="Ej. Heroica Puebla de Zaragoza"
                    :error="$errors->first('municipality')"
                    class="input-primary"
            />
        </div>

        {{-- Colonia --}}
        <div>
            <x-mary-input
                label="Colonia"
                icon="o-home"
                wire:model="colony"
                placeholder="Ej. Centro"
                :error="$errors->first('colony')"
                class="input-primary"
            />
        </div>

        {{-- Calle --}}
        <div>
            <x-mary-input
                label="Calle"
                icon="o-arrow-long-right"
                wire:model="street"
                placeholder="Ej. Avenida Principal"
                :error="$errors->first('street')"
                class="input-primary"
            />
        </div>

        {{-- Número Exterior --}}
        <div>
            <x-mary-input
                label="Número Exterior"
                icon="o-hashtag"
                wire:model="exterior_number"
                placeholder="Ej. 123"
                maxlength="20"
                :error="$errors->first('exterior_number')"
                class="input-primary"
            />
        </div>

        {{-- Número Interior --}}
        <div>
            <x-mary-input
                label="Número Interior (Opcional)"
                icon="o-hashtag"
                wire:model="interior_number"
                placeholder="Ej. A"
                maxlength="20"
                :error="$errors->first('interior_number')"
                class="input-primary"
            />
        </div>

        {{-- Código Postal --}}
        <div>
            <x-mary-input
                label="Código Postal"
                icon="o-envelope-open"
                wire:model="postal_code"
                placeholder="Ej. 28001"
                maxlength="5"
                pattern="\d{5}"
                :error="$errors->first('postal_code')"
                class="input-primary"
            />
        </div>
    </div>

    {{-- Indicador de errores en la pestaña --}}
    @if($tabErrors['billing'] ?? 0)
        <div class="alert alert-warning">
            <x-mary-icon name="o-exclamation-triangle" class="w-4 h-4" />
            <span>Hay {{ $tabErrors['billing'] }} campo(s) con error en esta sección.</span>
        </div>
    @endif
</div>

