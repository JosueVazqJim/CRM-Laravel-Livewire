<x-layouts::auth :title="__('Confirm password')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Confirmar contraseña')"
            :description="__('Esta es una zona segura de la aplicación. Por favor, confirme su contraseña antes de continuar.')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf
            <x-mary-password
                    name="password"
                    label="Contraseña"
                    placeholder="Ingresa tu contraseña"
                    icon="o-lock-closed"
                    required
                    autocomplete="current-password"
                    right
                    class="input-primary"
                    :error="$errors->has('password')"
                    :error-message="$errors->first('password')"
            />

            {{-- Mostrar errores generales (si existen) --}}
            @if ($errors->any())
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mt-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ __('Por favor corrige los siguientes errores:') }}</p>
                            <ul class="mt-2 text-sm text-red-700 dark:text-red-300 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <x-mary-button
                    type="submit"
                    label="Confirmar"
                    icon="o-paper-airplane"
                    class="btn-primary w-full"
                    spinner="submit"
                    data-test="confirm-password-button"
            />
        </form>



    </div>
</x-layouts::auth>
