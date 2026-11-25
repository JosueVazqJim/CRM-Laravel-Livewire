<?php

namespace App\Concerns;

use App\Models\Users\User;
use Illuminate\Validation\Rule;

trait ProfileValidationSingleRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>>
     */
    protected function profileSingleRules(?int $userId = null): array
    {
        return [
            'first_name' => $this->nameRules(),
            'paternal_surname' => ['nullable', 'string', 'max:255'],
            'maternal_surname' => ['nullable', 'string', 'max:255'],
            'email' => $this->emailRules($userId),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    protected function sexRules(): array
    {
        // solo se aceptan 'M', 'F', 'O'
        return ['required', 'in:M,F,O'];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function rfcRules(?int $userId = null): array
    {
        $unique = $userId === null
            ? Rule::unique(User::class, 'rfc')
            : Rule::unique(User::class, 'rfc')->ignore($userId);

        return [
            'required',
            'string',
            'regex:/^[A-Z0-9]{13}$/', // exactamente 13 caracteres (A-Z y 0-9)
            $unique,
        ];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }
}
