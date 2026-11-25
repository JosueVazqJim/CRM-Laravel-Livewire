<section class="w-full">
    @include('partials.settings-heading')

{{--    <flux:heading class="sr-only">{{ __('Password settings') }}</flux:heading>--}}

    <x-settings.layout :heading="__('Actualizar contraseña')" :subheading="__('Asegura tu cuenta usando una contraseña larga y aleatoria para mantener la seguridad de tu cuenta.')">
        <x-mary-form wire:submit="updatePassword" class="my-6 w-full space-y-6">
            <x-mary-input label="Contraseña actual" wire:model="current_password" type="password" required autocomplete="current-password" class="input-primary" />

            <x-mary-input label="Nueva contraseña" wire:model="password" type="password" required autocomplete="new-password" class="input-primary" />

            <x-mary-input label="Confirme contraseña" wire:model="password_confirmation" type="password" required autocomplete="new-password" class="input-primary" />

                <x-slot:actions>
                    <x-mary-button label="Guardar" class="btn-primary" type="submit" spinner="updatePassword"  />
                </x-slot:actions>
        </x-mary-form>
    </x-settings.layout>
</section>
