<?php

namespace App\Livewire\Settings;

use App\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Password settings')]
class Password extends Component
{
    use PasswordValidationRules;
    use Toast;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {

        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);

            Auth::user()->update([
                'password' => $validated['password'],
            ]);

            $this->reset('current_password', 'password', 'password_confirmation');

            $this->success(
                title: '¡Contraseña actualizada!',
                description: 'Tu contraseña ha sido cambiada exitosamente.',
                position: 'toast-top toast-end',
                icon: 'o-check-circle',
                timeout: 3000
            );

            $this->dispatch('password-updated');

        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            $this->error(
                title: 'Error de validación',
                description: 'Por favor verifica los datos ingresados.',
                position: 'toast-top toast-end',
                icon: 'o-exclamation-triangle',
                timeout: 4000
            );

            throw $e;
        } catch (\Exception $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            // ❌ Toast de error general
            $this->error(
                title: 'Error inesperado',
                description: 'No se pudo actualizar la contraseña. Intenta de nuevo.',
                position: 'toast-top toast-end',
                icon: 'o-shield-exclamation',
                timeout: 5000
            );
        }
    }
}
