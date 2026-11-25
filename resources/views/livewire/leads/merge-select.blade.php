<section class="w-full">
    <x-mary-header title="Fusionar lead" :subtitle="'Selecciona con qué leads fusionar • ' . ($lead->name_company ?? 'Sin empresa')" separator>
        <x-slot:actions>
            <x-mary-button label="Cancelar" icon="o-arrow-left" :link="route('leads.show', $lead)" class="btn-ghost" />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-input
            placeholder="Buscar por empresa, estado, clasificación, contacto..."
            wire:model.live.debounce.500ms="search"
            icon="o-magnifying-glass"
            class="input-primary mb-2"
            clearable
    />

    <x-mary-card class="mb-2">
        <div class="flex items-center gap-2 text-sm">
            <x-mary-badge value="Lead base" class="badge-primary" />
            <span class="font-semibold">{{ $lead->name_company ?: 'Lead #'.$lead->id }}</span>
            <span class="text-gray-500 dark:text-gray-400">Solo se muestran leads de la misma empresa cliente que este lead.</span>
        </div>
    </x-mary-card>

    <div class="flex justify-end mb-2">
        <x-mary-button
            label="Continuar con {{ count($selectedLeadIds) + 1 }} leads"
            icon="o-arrow-right"
            :link="count($selectedLeadIds) >= 1 ? $mergeUrl : null"
            @disabled(count($selectedLeadIds) < 1)
            class="btn-primary"
        />
    </div>

    <x-mary-card>
        <x-mary-table
                :headers="$headers"
                :rows="$candidates"
                with-pagination
                striped
        >
            @scope('cell_select', $candidateLead)
                <input type="checkbox" class="checkbox checkbox-sm"
                       wire:model.live="selectedLeadIds" value="{{ $candidateLead->id }}" />
            @endscope

            @scope('cell_state', $candidateLead)
                {{ $candidateLead->state->name ?? '—' }}
            @endscope

            @scope('cell_contact', $candidateLead)
                {{ $candidateLead->contactPeople->first()->name_contact ?? '—' }}
            @endscope

            @scope('cell_classification', $candidateLead)
                <x-mary-badge :value="$candidateLead->classification ? ucfirst($candidateLead->classification) : '—'" class="badge-secondary" />
            @endscope

            @scope('cell_funnel_stage', $candidateLead)
                {{ $candidateLead->funnel_stage ? ucfirst($candidateLead->funnel_stage) : '—' }}
            @endscope

            @scope('cell_creat_at', $candidateLead)
                {{ $candidateLead->creat_at?->format('d/m/Y H:i') ?? 'N/A' }}
            @endscope

            <x-slot:empty>
                <x-mary-icon name="o-inbox" label="No se encontraron leads de la misma empresa." />
            </x-slot:empty>
        </x-mary-table>
    </x-mary-card>
</section>
