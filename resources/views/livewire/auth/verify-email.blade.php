<x-layouts::auth :title="__('Email verification')">
    <div class="mt-4 flex flex-col gap-6">
        <flux:text class="text-center">
            {{ __('Verifique su dirección de correo electrónico haciendo clic en el enlace que le acabamos de enviar por correo electrónico.') }}
        </flux:text>

        @if (session('status') == 'verification-link-sent')
            <flux:text class="text-center font-medium !dark:text-green-400 !text-green-600">
                {{ __('Se ha enviado un nuevo enlace de verificación a la dirección de correo electrónico que proporcionó durante el registro.') }}
            </flux:text>
        @endif

        <div class="flex flex-col items-center justify-between space-y-3">
{{--            <form method="POST" action="{{ route('verification.send') }}">--}}
{{--                @csrf--}}
{{--                <flux:button type="submit" variant="primary" class="w-full">--}}
{{--                    {{ __('Resend verification email') }}--}}
{{--                </flux:button>--}}
{{--            </form>--}}

            <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                @csrf
                <x-mary-button
                        type="submit"
                        label="Reenviar correo de verificación"
                        icon="o-paper-airplane"
                        class="btn-primary w-full"
                        spinner="send"
                />
            </form>

{{--            <form method="POST" action="{{ route('logout') }}">--}}
{{--                @csrf--}}
{{--                <flux:button variant="ghost" type="submit" class="text-sm cursor-pointer" data-test="logout-button">--}}
{{--                    {{ __('Log out') }}--}}
{{--                </flux:button>--}}
{{--            </form>--}}

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <x-mary-button
                        type="submit"
                        label="Cerrar sesión"
                        icon="o-power"
                        class="btn-ghost w-full text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
                        spinner="logout"
                />
            </form>
        </div>
    </div>
</x-layouts::auth>
