@props(['lead'])

<x-mary-card class="shadow-xl">
    <x-slot:title>
        <div class="flex items-center gap-2">
            <x-mary-icon name="o-chart-bar" class="h-5 w-5"></x-mary-icon>
            <span>Diagnóstico y Proceso</span>
        </div>
    </x-slot:title>

    <div class="space-y-3">
        {{-- Problema Detectado --}}
        <div class="flex items-center justify-between">
            <label class="text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Problema Detectado</label>
            <x-mary-badge
                :value="$lead->problem_detected ? 'Sí' : 'No'"
                class="badge {{ $lead->problem_detected ? 'badge-success' : 'badge-error' }}"
            />
        </div>
        <p> {{$lead->problem_detected ?? ''}}</p>

        {{-- Demo Ofrecida --}}
        <div class="flex items-center justify-between">
            <label class="text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Demo Ofrecida</label>
            <x-mary-badge
                :value="$lead->demo_offered ? 'Sí' : 'No'"
                class="badge {{ $lead->demo_offered ? 'badge-success' : 'badge-error' }}"
            />
        </div>

        {{-- Primer Contacto --}}
        <div class="flex items-center justify-between">
            <label class="text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Primer Contacto</label>
            <x-mary-badge
                :value="$lead->first_contact ? 'Sí' : 'No'"
                class="badge {{ $lead->first_contact ? 'badge-success' : 'badge-error' }}"
            />
        </div>

        {{-- Diferenciador Explicado --}}
        <div class="flex items-center justify-between">
            <label class="text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Diferenciador Explicado</label>
            <x-mary-badge
                :value="$lead->differentiator_explained ? 'Sí' : 'No'"
                class="badge {{ $lead->differentiator_explained ? 'badge-success' : 'badge-error' }}"
            />
        </div>

        {{-- Diagnóstico Realizado --}}
        <div class="flex items-center justify-between">
            <label class="text-xs font-semibold text-gray-800 dark:text-gray-400 mt-2">Diagnóstico Realizado</label>
            <x-mary-badge
                :value="$lead->diagnosis_made ? 'Sí' : 'No'"
                class="badge {{ $lead->diagnosis_made ? 'badge-success' : 'badge-error' }}"
            />
        </div>
    </div>
</x-mary-card>

