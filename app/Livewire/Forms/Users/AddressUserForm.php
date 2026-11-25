<?php

namespace App\Livewire\Forms\Users;

use App\Concerns\AddressUserValidationRules;
use App\Models\Users\User;
use App\Models\Users\UserAddress;
use Livewire\Form;

class AddressUserForm extends Form
{
    use AddressUserValidationRules;

    public ?UserAddress $addressModel = null;

    public string $type = '';

    public string $state = '';

    public string $municipality = '';

    public string $colony = '';

    public string $street = '';

    public string $exterior_number = '';

    public string $interior_number = '';

    public string $postal_code = '';

    public function rules(): array
    {
        return $this->addressUserRules();
    }

    public function setAddress(?UserAddress $address): void
    {
        if (! $address) {
            return;
        }

        $this->addressModel = $address;

        $this->type = $address->type ?? '';
        $this->state = $address->state ?? '';
        $this->municipality = $address->municipality ?? '';
        $this->colony = $address->colony ?? '';
        $this->street = $address->street ?? '';
        $this->exterior_number = $address->exterior_number ?? '';
        $this->interior_number = $address->interior_number ?? '';
        $this->postal_code = $address->postal_code ?? '';
    }

    public function store(User $user): UserAddress
    {
        $data = [
            'type' => $this->type,
            'state' => $this->state,
            'municipality' => $this->municipality,
            'colony' => $this->colony,
            'street' => $this->street,
            'exterior_number' => $this->exterior_number,
            'interior_number' => $this->interior_number,
            'postal_code' => $this->postal_code,
        ];

        return $user->address()->create($data);
    }

    public function update(): UserAddress
    {
        $this->addressModel->update([
            'type' => $this->type,
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
