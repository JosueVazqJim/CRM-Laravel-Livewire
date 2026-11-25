<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Iniciar sesión')"
                       :description="__('Introduce tu correo electrónico y tu contraseña a continuación para iniciar sesión')"
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            {{-- Email Address --}}
            <x-mary-input
                    name="email"
                    label="Dirección de correo electrónico"
                    placeholder="email@example.com"
                    icon="o-envelope"
                    value="admin@demo.test"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    class="input-primary"
                    :error="$errors->has('email')"
                    :error-message="$errors->first('email')"
            />

            {{-- Password --}}
            <div class="relative">
                <x-mary-password
                        name="password"
                        label="Contraseña"
                        placeholder="Ingresa tu contraseña"
                        icon="o-lock-closed"
                        required
                        autocomplete="current-password"
                        value="password"
                        right
                        class="input-primary"
                        :error="$errors->has('password')"
                        :error-message="$errors->first('password')"
                />

                @if (Route::has('password.request'))
                    <a
                            href="{{ route('password.request') }}"
                            class="absolute top-0 text-sm end-0 text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300"
                            wire:navigate
                    >
                        {{ __('¿Olvidaste tu contraseña?') }}
                    </a>
                @endif
            </div>

            {{-- Display General Errors --}}
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

            {{-- Session Status --}}
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                {{ session('status') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Remember Me --}}
            <div class="flex items-center">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input
                            type="checkbox"
                            name="remember"
                            class="checkbox checkbox-primary checkbox-sm"
                            {{ old('remember') ? 'checked' : '' }}
                    >
                    <span class="text-sm text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-200 transition">
                {{ __('Recordarme') }}
            </span>
                </label>
            </div>

            {{-- Submit Button --}}
            <div class="flex items-center justify-end">
                <x-mary-button
                        type="submit"
                        label="Iniciar sesión"
                        icon="o-paper-airplane"
                        class="btn-primary w-full"
                        spinner="submit"
                />
            </div>

            {{-- Register Link --}}
{{--            @if (Route::has('register'))--}}
{{--                <div class="text-center text-sm text-gray-500 dark:text-gray-400 mt-2">--}}
{{--                    ¿No tienes una cuenta?--}}
{{--                    <a--}}
{{--                            href="{{ route('register') }}"--}}
{{--                            class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 font-medium"--}}
{{--                            wire:navigate--}}
{{--                    >--}}
{{--                        Regístrate aquí--}}
{{--                    </a>--}}
{{--                </div>--}}
        </form>

{{--        @if (Route::has('register'))--}}
{{--            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">--}}
{{--                <span>{{ __('Don\'t have an account?') }}</span>--}}
{{--                <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>--}}
{{--            </div>--}}
{{--        @endif--}}
    </div>
</x-layouts::auth>
