<?php

namespace App\Concerns;

trait AddressCompanyValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>>
     */
    protected function addressCompanyRules(?int $companyID = null): array
    {
        return [
            'state' => $this->addressCompanyNameRules(),
            'municipality' => $this->addressCompanyNameRules(),
            'colony' => $this->addressCompanyNameRules(),
            'street' => $this->addressCompanyNameRules(),
            'exterior_number' => $this->addressCompanyNameRules(),
            'interior_number' => ['nullable'],
            'postal_code' => $this->addressCompanyNameRules(),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function addressCompanyNameRules(): array
    {
        return ['required'];
    }
}
