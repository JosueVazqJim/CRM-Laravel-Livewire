@props(['company'])

<x-mary-card class="shadow-xl">
    <x-slot:title>
        <div class="flex items-center gap-2">
            <x-mary-icon name="o-building-office" class="w-5 h-5" />
            <span>Direcciones de correo electrónico</span>
            <x-mary-badge value="{{ $company->companyEmails->count() }}" class="badge-primary badge-md" />
        </div>
    </x-slot:title>

    @if($company->companyEmails && $company->companyEmails->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($company->companyEmails as $email)
                <div class="flex items-center gap-3 p-2 rounded-lg hover:border-primary/30 transition-colors">
                    <x-mary-button
                            icon="o-envelope"
                            :link="'mailto:' . $email->email"
                            class="btn-ghost btn-lg w-full justify-start text-base"
                            external
                    >
                        <div class="flex-1">
                            <p class="font-medium text-base">{{ $email->email }}</p>
                            @if($email->description)
                                <p class="text-sm text-gray-500">{{ $email->description }}</p>
                            @endif
                        </div>
                    </x-mary-button>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-8 text-center">
            <x-mary-icon name="o-envelope" class="w-12 h-12 text-gray-300 mb-3" />
            <p class="text-gray-500">No hay direcciones de correo registradas</p>
            <p class="text-sm text-gray-400 mt-1">Agrega correos electrónicos para esta empresa</p>
        </div>
    @endif
</x-mary-card>