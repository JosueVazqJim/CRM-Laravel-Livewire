<?php

namespace App\Livewire\Leads\ComponentsShow;

use App\Models\Leads\BillingAddress;
use App\Models\Leads\BillingInfo;
use App\Models\Leads\Lead;
use Livewire\Component;
use Mary\Traits\Toast;

class Billing extends Component
{
    use Toast;

    public Lead $lead;

    public BillingInfo $billingInfo;

    public BillingAddress $billingAddress;

    public bool $editing = false;

    // Propiedades de datos - DOMICILIO (BillingInfo)
    public string $business_name;

    public string $rfc;

    public string $billing_email;

    // Propiedades de datos - DOMICILIO (BillingAddress)
    public string $address_state;

    public string $municipality;

    public string $colony;

    public string $street;

    public string $exterior_number;

    public string $interior_number;

    public string $postal_code;

    public bool $loadingMunicipalities = false;

    public function mount(Lead $lead)
    {
        $this->lead = $lead;

        $this->billingInfo = $lead->billingInfo;

        // Obtener o crear BillingAddress relacionado
        $this->billingAddress = $this->billingInfo->address;

        $this->fillFields();
    }

    public function fillFields()
    {
        $this->business_name = $this->billingInfo->business_name ?? '';
        $this->rfc = $this->billingInfo->rfc ?? '';
        $this->billing_email = $this->billingInfo->email ?? '';

        $this->address_state = $this->billingAddress->state ?? '';
        $this->municipality = $this->billingAddress->municipality ?? '';
        $this->colony = $this->billingAddress->colony ?? '';
        $this->street = $this->billingAddress->street ?? '';
        $this->exterior_number = $this->billingAddress->exterior_number ?? '';
        $this->interior_number = $this->billingAddress->interior_number ?? '';
        $this->postal_code = $this->billingAddress->postal_code ?? '';
    }

    public function enableEditing()
    {
        $this->editing = true;
    }

    public function cancelEditing()
    {
        $this->editing = false;
        $this->fillFields(); // Restaurar valores originales
    }

    public function update()
    {
        $this->validate([
            // Facturación
            'business_name' => ['nullable', 'string', 'max:255'],
            'rfc' => ['nullable', 'string', 'max:20'],
            'billing_email' => ['nullable', 'email', 'max:255'],

            // Domicilio
            'address_state' => ['nullable', 'string', 'max:100'],
            'municipality' => ['nullable', 'string', 'max:100'],
            'colony' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'exterior_number' => ['nullable', 'string', 'max:20'],
            'interior_number' => ['nullable', 'string', 'max:20'],
            'postal_code' => ['nullable', 'string', 'regex:/^\d{5}$/'],
        ]);

        try {
            // 1. BillingAddress primero (billing_infos.address_id es NOT NULL)
            $addressData = [
                'state' => $this->address_state,
                'municipality' => $this->municipality,
                'colony' => $this->colony,
                'street' => $this->street,
                'exterior_number' => $this->exterior_number,
                'interior_number' => $this->interior_number,
                'postal_code' => $this->postal_code,
            ];

            if ($this->billingAddress->exists) {
                $this->billingAddress->update($addressData);
            } else {
                $this->billingAddress = BillingAddress::create($addressData);
            }

            // 2. BillingInfo con el address_id ya disponible
            $billingInfoData = [
                'business_name' => $this->business_name,
                'rfc' => $this->rfc,
                'email' => $this->billing_email,
                'address_id' => $this->billingAddress->id,
            ];

            if ($this->billingInfo->exists) {
                $this->billingInfo->update($billingInfoData);
            } else {
                $this->billingInfo = BillingInfo::create($billingInfoData);
                $this->lead->update(['billing_id' => $this->billingInfo->id]);
            }

            // 3. Sincronizar la relación en $lead para que la vista en modo display refleje los cambios
            $this->lead->refresh();

            $this->editing = false;

            $this->success(
                title: '¡Campos de Información de Facturación actualizados!',
                description: 'El lead ha sido guardado exitosamente.',
                timeout: 3000,
            );
        } catch (\Throwable $e) {
            $this->error(
                title: 'Error al actualizar el lead',
                description: $e->getMessage()
            );
        }
    }

    public function render()
    {
        return view('livewire.leads.components-show.billing');
    }
}
