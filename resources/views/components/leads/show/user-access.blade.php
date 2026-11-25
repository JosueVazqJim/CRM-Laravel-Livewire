@props(['lead'])

@if($lead->userAccess->count() > 0)
    <x-mary-card class="shadow-xl">
        <x-slot:title>
            <div class="flex items-center gap-2">
                <x-mary-icon name="o-lock-closed" class="h-5 w-5"></x-mary-icon>
                <span>Accesos de Usuario</span>
                <x-mary-badge value="{{ $lead->userAccess->count() }}" class="badge-primary badge-sm" />
            </div>
        </x-slot:title>

        <div class="space-y-2">
            @foreach($lead->userAccess as $access)
                <div class="flex items-center justify-between p-3 bg-base-100 rounded border border-base-300 hover:border-primary transition-colors">
                    <div class="flex items-center gap-2">
                        <x-mary-avatar
                            :image="$access->user?->avatar_url ?? asset('images/empty-user.jpg')"
                            class="!w-8 !h-8"
                        />
                        <div>
                            <p class="font-semibold text-sm">
                                {{ $access->user?->first_name }}
                                {{ $access->user?->paternal_surname }}
                                {{ $access->user?->maternal_surname }}
                            </p>
                            <p class="text-xs text-gray-800 dark:text-gray-400">{{ $access->user?->email }}</p>
                        </div>
                    </div>
{{--                    <x-mary-badge--}}
{{--                        :value="ucfirst($access->permission_level ?? 'sin permisos')"--}}
{{--                        class="badge badge-outline"--}}
{{--                    />--}}
                </div>
            @endforeach
        </div>
    </x-mary-card>
@endif

