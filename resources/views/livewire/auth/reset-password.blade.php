<x-layouts::auth :title="__('Reset password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Restablecer contraseña')" :description="__('Por favor, ingrese su nueva contraseña a continuación')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
{{--            <flux:input--}}
{{--                    name="email"--}}
{{--                    value="{{ request('email') }}"--}}
{{--                    :label="__('Email')"--}}
{{--                    type="email"--}}
{{--                    required--}}
{{--                    autocomplete="email"--}}
{{--            />--}}

            <x-mary-input
                    name="email"
                    label="Dirección de correo electrónico"
                    icon="o-envelope"
                    value="{{ request('email') }}"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    class="input-primary"
            />

            <!-- Password -->
{{--            <flux:input--}}
{{--                name="password"--}}
{{--                :label="__('Password')"--}}
{{--                type="password"--}}
{{--                required--}}
{{--                autocomplete="new-password"--}}
{{--                :placeholder="__('Password')"--}}
{{--                viewable--}}
{{--            />--}}

            {{-- Password --}}
            <x-mary-password
                    name="password"
                    label="Contraseña"
                    placeholder="Ingresa tu contraseña"
                    icon="o-lock-closed"
                    required
                    autocomplete="new-password"
                    right
                    class="input-primary"
                    :error="$errors->has('password')"
                    :error-message="$errors->first('password')"
            />

            <!-- Confirm Password -->
{{--            <flux:input--}}
{{--                name="password_confirmation"--}}
{{--                :label="__('Confirm password')"--}}
{{--                type="password"--}}
{{--                required--}}
{{--                autocomplete="new-password"--}}
{{--                :placeholder="__('Confirm password')"--}}
{{--                viewable--}}
{{--            />--}}

            <x-mary-password
                    name="password_confirmation"
                    label="Confirmar contraseña"
                    icon="o-lock-closed"
                    required
                    autocomplete="new-password"
                    right
                    class="input-primary"
                    :error="$errors->has('password')"
                    :error-message="$errors->first('password')"
            />

            @if ($errors->any())
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                {{ __('Por favor corrige los siguientes errores:') }}
                            </h3>
                            <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-end">
{{--                <flux:button type="submit" variant="primary" class="w-full" data-test="reset-password-button">--}}
{{--                    {{ __('Reset password') }}--}}
{{--                </flux:button>--}}

                <x-mary-button
                        type="submit"
                        label="Restablecer contraseña"
                        class="btn-primary w-full"
                        spinner="submit"
                        data-test="reset-password-button"
                />
            </div>
        </form>
    </div>
</x-layouts::auth>
