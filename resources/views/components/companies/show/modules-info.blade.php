@props(['company'])

<x-mary-card class="shadow-xl">
    <x-slot:title>
        <div class="flex items-center gap-2">
            <x-mary-icon name="o-briefcase" class="w-5 h-5" />
            <span>Módulos que administra</span>
            <x-mary-badge value="{{ $company->modules->count() }}" class="badge-primary badge-sm" />
        </div>
    </x-slot:title>

    <x-slot:subtitle>
        Módulos habilitados para la empresa
    </x-slot:subtitle>

    @if($company->modules && $company->modules->count() > 0)
        <div class="grid grid-cols-1 gap-3">
            @foreach($company->modules as $module)
                <div class="flex items-center gap-3 p-3 border border-base-300 rounded-lg">
                    <div class="bg-primary/10 p-2 rounded-lg">
                        <x-mary-icon name="o-briefcase" class="w-5 h-5" />
                    </div>
                    <div class="flex-1">
                        <p class="font-medium">{{ $module->name }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500 text-center py-4">No hay módulos habilitados para la empresa</p>
    @endif
</x-mary-card>