@props(['user'])

<x-mary-card class="shadow-xl">
    <x-slot:title>
        <div class="flex items-center gap-2">
            <x-mary-icon name="o-building-office" class="w-5 h-5" />
            <span>Empresas que administra</span>
            <x-mary-badge value="{{ $user->companies->count() }}" class="badge-primary badge-sm" />
        </div>
    </x-slot:title>

    <x-slot:subtitle>
        Empresas que el usuario puede administrar
    </x-slot:subtitle>

    @if($user->companies && $user->companies->count() > 0)
        <div class="grid grid-cols-1 gap-3">
            @foreach($user->companies as $company)
                <div class="flex items-center gap-3 p-3 border border-base-300 rounded-lg">
                    <div class="bg-primary/10 p-2 rounded-lg">
                        <x-mary-icon name="o-building-office" class="w-5 h-5" />
                    </div>
                    <div class="flex-1">
                        <p class="font-medium">{{ $company->name }}</p>
                    </div>
                    <x-mary-button
                            icon="o-arrow-right"
                            class="btn-ghost btn-sm"
                            :link="url('companies/' . $company->slug)"
                    />
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500 text-center py-4">No administra ninguna empresa</p>
    @endif
</x-mary-card>