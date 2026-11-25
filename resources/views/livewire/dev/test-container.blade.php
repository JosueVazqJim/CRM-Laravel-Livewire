<div class="p-10 border space-y-6">

{{--    <x-mary-header title="Test Container" separator />--}}

{{--    --}}{{-- Indicador de carga SOLO para este componente --}}
{{--    <div wire:loading class="w-full">--}}
{{--        <progress class="progress progress-primary w-full"></progress>--}}
{{--    </div>--}}

    <x-mary-header
            title="Test Container"
            separator
            progress-indicator
            wire:target="setSection"
    />

    <div class="flex gap-4">

        <button wire:click="setSection('a')" class="btn btn-primary">
            Componente A
        </button>

        <button wire:click="setSection('b')" class="btn btn-primary">
            Componente B
        </button>

        <button wire:click="setSection('c')" class="btn btn-primary">
            Componente C
        </button>

    </div>

    <div class="p-6 border rounded">

        @if($section === 'a')
            <livewire:dev.test-a />
        @endif

        @if($section === 'b')
            <livewire:dev.test-b />
        @endif

        @if($section === 'c')
            <livewire:dev.test-c />
        @endif

    </div>

</div>