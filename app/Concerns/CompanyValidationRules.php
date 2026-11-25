<?php

namespace App\Concerns;

use App\Models\Companies\Company;
use Illuminate\Contracts\Validation\Rule;

trait CompanyValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, Rule|array<mixed>|string>>
     */
    protected function companyRules(?int $companyID = null): array
    {
        return [
            'name' => $this->nameRules(),
            'slug' => $this->slugRules($companyID),
            'website' => $this->websiteRules(),
            'theme' => $this->nameRules(),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, Rule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, Rule|array<mixed>|string>
     */
    protected function websiteRules(): array
    {
        return ['nullable', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, Rule|array<mixed>|string>
     */
    protected function slugRules(?int $companyId = null): array
    {
        $unique = $companyId === null
            ? \Illuminate\Validation\Rule::unique(Company::class, 'slug')
            : \Illuminate\Validation\Rule::unique(Company::class, 'slug')->ignore($companyId);

        return [
            'required',
            'string',
            $unique,
        ];
    }
}
