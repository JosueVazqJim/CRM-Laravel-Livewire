<?php

namespace App\Livewire\Forms\Companies;

use App\Concerns\AddressCompanyValidationRules;
use App\Models\Companies\Company;
use App\Models\Companies\CompanyAddress;
use Livewire\Form;

class AddressCompanyForm extends Form
{
    use AddressCompanyValidationRules;

    public ?CompanyAddress $addressModel = null;

    public string $state = '';

    public string $municipality = '';

    public string $colony = '';

    public string $street = '';

    public string $exterior_number = '';

    public string $interior_number = '';

    public string $postal_code = '';

    public function rules(): array
    {
        return $this->addressCompanyRules();
    }

    public function setAddress(?CompanyAddress $address): void
    {
        if (! $address) {
            return;
        }

        $this->addressModel = $address;

        $this->state = $address->state ?? '';
        $this->municipality = $address->municipality ?? '';
        $this->colony = $address->colony ?? '';
        $this->street = $address->street ?? '';
        $this->exterior_number = $address->exterior_number ?? '';
        $this->interior_number = $address->interior_number ?? '';
        $this->postal_code = $address->postal_code ?? '';
    }

    public function store(Company $company): CompanyAddress
    {
        $data = [
            'state' => $this->state,
            'municipality' => $this->municipality,
            'colony' => $this->colony,
            'street' => $this->street,
            'exterior_number' => $this->exterior_number,
            'interior_number' => $this->interior_number,
            'postal_code' => $this->postal_code,
        ];

        return $company->companyAddress()->create($data);
    }

    public function update(): CompanyAddress
    {
        $this->addressModel->update([
            'state' => $this->state,
            'municipality' => $this->municipality,
            'colony' => $this->colony,
            'street' => $this->street,
            'exterior_number' => $this->exterior_number,
            'interior_number' => $this->interior_number,
            'postal_code' => $this->postal_code,
        ]);

        return $this->addressModel->fresh(); // retorna el modelo actualizado desde la base de datos
    }
}
