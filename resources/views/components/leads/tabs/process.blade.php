@props(['problem_detected' => '', 'demo_offered' => false, 'first_contact' => false, 'differentiator_explained' => false, 'diagnosis_made' => false, 'tabErrors' => []])

<x-slot:label>
    <div class="flex items-center gap-1 whitespace-nowrap">
        @if($tabErrors['process'] ?? false)
            <x-mary-badge value="{{ $tabErrors['process'] }}" class="badge-error badge-sm"/>
        @endif
    </div>
</x-slot:label>

<div class="space-y-4">
    {{-- Problema Detectado --}}
    <div>
        <x-mary-textarea
            label="Problema Detectado"
            icon="o-exclamation-circle"
            wire:model="problem_detected"
            placeholder="Describe el problema o necesidad del cliente..."
            rows="5"
            :error="$errors->first('problem_detected')"
            class="input-primary"
        />
    </div>

    {{-- Checkboxes de Proceso --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Demo Ofrecida --}}
        <div class="form-control">
            <label class="cursor-pointer label gap-2">
                <x-mary-checkbox wire:model="demo_offered" />
                <span class="label-text">Demo Ofrecida</span>
            </label>
            @error('demo_offered')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- Primer Contacto --}}
        <div class="form-control">
            <label class="cursor-pointer label gap-2">
                <x-mary-checkbox wire:model="first_contact" />
                <span class="label-text">Primer Contacto Realizado</span>
            </label>
            @error('first_contact')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- Diferenciador Explicado --}}
        <div class="form-control">
            <label class="cursor-pointer label gap-2">
                <x-mary-checkbox wire:model="differentiator_explained" />
                <span class="label-text">Diferenciador Explicado</span>
            </label>
            @error('differentiator_explained')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- Diagnóstico Realizado --}}
        <div class="form-control">
            <label class="cursor-pointer label gap-2">
                <x-mary-checkbox wire:model="diagnosis_made" />
                <span class="label-text">Diagnóstico Realizado</span>
            </label>
            @error('diagnosis_made')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Indicador de errores en la pestaña --}}
    @if($tabErrors['process'] ?? 0)
        <div class="alert alert-warning">
            <x-mary-icon name="o-exclamation-triangle" class="w-4 h-4" />
            <span>Hay {{ $tabErrors['process'] }} campo(s) con error en esta sección.</span>
        </div>
    @endif
</div>

