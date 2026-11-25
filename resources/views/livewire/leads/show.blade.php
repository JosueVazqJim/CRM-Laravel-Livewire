<section class="w-full">
    {{-- Header --}}
    <x-mary-header title="Detalles del Lead" :subtitle="'Lead • ' . ($lead->name_company ?? 'Sin empresa')" separator>
        <x-slot:actions>
            <x-mary-button label="Volver" icon="o-arrow-left" :link="route('leads.index')" class="btn-ghost" />
            <x-mary-button label="Fusionar" icon="o-arrows-pointing-in" :link="route('leads.merge.select', $lead)" class="btn-ghost" />
{{--                <x-mary-button label="Actualizar" icon="o-pencil" :link="route('leads.update', $lead)" class="btn-primary" />--}}
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- COLUMNA IZQUIERDA --}}
        <div class="lg:col-span-1">
            <div class="sticky top-8 space-y-6">
{{--                <x-leads.show.general-info :lead="$lead" />--}}
                <livewire:leads.components-show.general-info :lead="$lead" />

{{--                <x-leads.show.commercial-info :lead="$lead" />--}}
                <livewire:leads.components-show.commercial-info :lead="$lead" />

{{--                <x-leads.show.client-status :lead="$lead" />--}}
                <livewire:leads.components-show.client-status :lead="$lead" />
            </div>
        </div>

        {{-- COLUMNA DERECHA --}}
        <div class="lg:col-span-2 space-y-6">
{{--            <x-leads.show.diagnosis-process :lead="$lead" />--}}
            <livewire:leads.components-show.diagnosis-process :lead="$lead" />

{{--            <x-leads.show.billing-info :lead="$lead" />--}}
            <livewire:leads.components-show.billing :lead="$lead" />

{{--            <x-leads.show.user-access :lead="$lead" />--}}
            <livewire:leads.components-show.user-access :lead="$lead" />

        </div>
    </div>

    {{-- Full Width Components --}}
    <div class="space-y-4 mt-4">
        {{-- 6. Contactos --}}
{{--        <x-leads.show.contacts :lead="$lead" />--}}
        <livewire:leads.components-show.contacts :lead="$lead" />

        <livewire:leads.components-show.notes :lead="$lead" />

        {{-- 7. Actividades --}}
{{--        <x-leads.show.activities :lead="$lead" />--}}
        <livewire:leads.components-show.activities :lead="$lead" :key="'actividades-'.$lead->id" />
        <livewire:leads.components-show.quotes-index :lead="$lead" :key="'quotes-' . $lead->id" />
{{--        <x-mary-button label="Agregar cotización" icon="o-newspaper" :link="route('quotes.create', $lead)" class="btn-ghost" />--}}

    </div>
</section>

