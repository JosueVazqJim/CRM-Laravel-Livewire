@props(['company'])

<x-mary-card class="shadow-xl">
    <x-slot:title>
        <div class="flex items-center gap-2">
            <x-mary-icon name="o-users" class="w-5 h-5" />
            <span>Usuarios</span>
            <x-mary-badge value="{{ $company->users->count() }}" class="badge-primary badge-md" />
        </div>
    </x-slot:title>

    <x-slot:subtitle>
        Usuarios vinculados a la empresa
    </x-slot:subtitle>


@if($company->users->count() > 0)
        <div class="grid grid-cols-1 gap-3">
            @foreach($company->users as $user)
                <a href="{{ route('users.show', $user->slug) }}"
                   class="block border border-base-300 rounded-lg p-4 cursor-pointer group">
                    <div class="flex items-start gap-3">
                        {{-- Avatar o icono --}}
                        <div class="relative">
                            <div class="bg-primary/10 rounded-full">
                                <x-mary-avatar :image="$user->avatar_url " class="!w-10" />
                            </div>
                        </div>

                        {{-- Información del usuario --}}
                        <div class="flex-1 space-y-1">
                            <div class="flex items-center justify-between">
                                <div class="text-lg font-medium group-hover:text-primary transition-colors">
                                    {{ $user->name }}
                                </div>
                                <x-mary-icon name="o-chevron-right" class="w-4 h-4 text-gray-400 group-hover:text-primary transition-colors" />
                            </div>

                            @if($user->email)
                                <div class="flex items-center gap-1 text-sm text-gray-600">
                                    <x-mary-icon name="o-envelope" class="w-3 h-3" />
                                    <span>{{ $user->email }}</span>
                                </div>
                            @endif

                            @if($user->role && $user->role->name)
                                <div class="flex items-center gap-1 text-sm">
                                    <x-mary-icon name="o-briefcase" class="w-3 h-3" />
                                    <span class="text-gray-600">{{ $user->role->name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-8 text-gray-500">
            <x-mary-icon name="o-user-group" class="w-12 h-12 mb-3 opacity-50" />
            <p>No hay usuarios vinculados a esta empresa</p>
        </div>
    @endif
</x-mary-card>