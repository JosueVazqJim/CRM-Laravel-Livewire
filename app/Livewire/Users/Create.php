<?php

namespace App\Livewire\Users;

use App\Livewire\Forms\Users\AddressUserForm;
use App\Livewire\Forms\Users\UserForm;
use App\Models\Companies\Company;
use App\Models\States\Municipality;
use App\Models\States\State;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Spatie\Permission\Models\Role;

#[Title('Create user')]
class Create extends Component
{
    use Toast;
    use WithFileUploads;

    public UserForm $user;

    public AddressUserForm $address;

    public array $companies = [];

    public $companyOptions = [];

    public string $tab = 'user';

    public $states = [];

    public $municipalities = [];

    public $roles = [];

    public $optionsRoles = [];

    public $loadingMunicipalities = false;

    public function mount(): void
    {
        $this->user = new UserForm($this, 'user');
        $this->address = new AddressUserForm($this, 'address');

        $this->companyOptions = Company::all()->map(fn ($company) => [
            'id' => $company->id,
            'name' => $company->name,
            'avatar' => $company->avatar_url,
        ])->toArray();

        $this->optionsRoles = Role::get()->map(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
        ])->toArray();

        $this->loadStates();

        if (! empty($this->address->state)) {
            $this->loadMunicipalities($this->address->state);
        }
    }

    /**
     * Cargar estados desde la API
     */
    protected function loadStates()
    {
        $this->states = State::all()->toArray();
    }

    /**
     * Cuando cambia el estado, cargar municipios
     */
    public function updatedAddressState($value)
    {
        $this->loadMunicipalities($value);
        $this->address->municipality = '';
    }

    /**
     * Cargar municipios para el estado seleccionado
     */
    protected function loadMunicipalities($stateId)
    {
        if (empty($stateId)) {
            $this->municipalities = [];

            return;
        }

        $this->loadingMunicipalities = true;

        // Cargar municipios usando el ID del estado
        $this->municipalities = Municipality::where('state_id', $stateId)->get()->toArray();

        $this->loadingMunicipalities = false;
    }

    protected function rules(): array
    {
        return [
            'companies' => ['nullable', 'array'],
            'companies.*' => ['exists:companies,id'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = $this->user->store();

        $this->address->store($user);

        $user->companies()->sync($this->companies);

        // Si roles vienen como IDs, obtener nombres para spatie
        if (! empty($this->roles)) {
            $roleNames = Role::whereIn('id', $this->roles)->pluck('name')->toArray();
            $user->syncRoles($roleNames);
        } else {
            // opcional: limpiar roles si no se seleccionó ninguno
            $user->syncRoles([]);
        }

        $this->success(
            title: '¡Usuario creado correctamente!',
            redirectTo: route('users.index')
        );
    }

    public function getTabErrors(): array
    {
        $errors = collect($this->getErrorBag()->toArray())->keys();

        return [
            'user' => $errors->filter(fn ($k) => str_starts_with($k, 'user.'))->count(),
            'companies' => $errors->filter(fn ($k) => str_starts_with($k, 'companies'))->count(),
            'address' => $errors->filter(fn ($k) => str_starts_with($k, 'address.'))->count(),
            'roles' => $errors->filter(fn ($k) => str_starts_with($k, 'roles.'))->count(),
        ];
    }

    public function addPhone(): void
    {
        // delega la acción al formulario anidado
        $this->user->addPhone();
    }

    public function removePhone(int $index): void
    {
        $this->user->removePhone($index);
    }

    public function render()
    {
        return view('livewire.users.create');
    }
}
