<?php

namespace App\Concerns;

trait AddressUserValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>>
     */
    protected function addressUserRules(?int $userId = null): array
    {
        return [
            'type' => $this->addressUserNameRules(),
            'state' => $this->addressUserNameRules(),
            'municipality' => $this->addressUserNameRules(),
            'colony' => $this->addressUserNameRules(),
            'street' => $this->addressUserNameRules(),
            'exterior_number' => $this->addressUserNameRules(),
            'interior_number' => ['nullable'],
            'postal_code' => $this->addressUserNameRules(),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function addressUserNameRules(): array
    {
        return ['required'];
    }
}
