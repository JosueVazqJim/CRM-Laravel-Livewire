@props(['company'])

<x-mary-card class="shadow-xl">
    <x-slot:title>
        <div class="flex items-center gap-2">
            <x-mary-icon name="o-phone" class="w-5 h-5" />
            <span>Teléfonos de contacto</span>
            <x-mary-badge value="{{ $company->companyPhones->count() }}" class="badge-primary badge-sm" />
        </div>
    </x-slot:title>

    <x-slot:subtitle>
        Números registrados de la empresa para contacto
    </x-slot:subtitle>

    @if($company->companyPhones && $company->companyPhones->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($company->companyPhones as $phone)
                <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                    <div class="bg-primary/10 p-2 rounded-full">
                        <x-mary-icon name="o-phone" class="w-4 h-4" />
                    </div>
                    <div>
                        <p class="font-medium">{{ $phone->phone }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500 text-center py-4">No hay teléfonos registrados</p>
    @endif
</x-mary-card>