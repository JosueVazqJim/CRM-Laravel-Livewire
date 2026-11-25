<?php

namespace App\Livewire\Users;

use App\Livewire\Forms\Users\AddressUserForm;
use App\Livewire\Forms\Users\UserForm;
use App\Models\Companies\Company;
use App\Models\States\Municipality;
use App\Models\States\State;
use App\Models\Users\User;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Spatie\Permission\Models\Role;

#[Title('Update user')]
class Update extends Component
{
    use Toast;
    use WithFileUploads;

    public UserForm $userForm;

    public AddressUserForm $address;

    public User $user;

    public array $companies = [];

    public $companyOptions = [];

    public string $tab = 'user';

    public $states = [];

    public $municipalities = [];

    public $roles = [];

    public $optionsRoles = [];

    public $loadingMunicipalities = false;

    public function mount(User $user): void
    {
        $this->user = $user;

        // Inicializar forms
        $this->userForm->setUser($user);
        $this->address->setAddress($user->address);

        // Cargar empresas asignadas
        $this->companies = $user->companies->pluck('id')->toArray();
        $this->companyOptions = Company::all()->map(fn ($company) => [
            'id' => $company->id,
            'name' => $company->name,
            'avatar' => $company->avatar_url,
        ]);

        $this->roles = $user->roles->pluck('id')->toArray();

        $this->optionsRoles = Role::get()->map(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
        ])->toArray();

        // Cargar estados
        $this->loadStates();

        // Si hay dirección, cargar municipios del estado guardado
        if ($user->address && ! empty($user->address->state)) {
            $this->loadMunicipalities($user->address->state);
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

    public function update(): void
    {
        $this->validate();

        // Actualizar usuario
        $user = $this->userForm->update();

        // Actualizar o crear dirección
        if ($this->user->address) {
            $this->address->update();
        } else {
            $this->address->store($user);
        }

        // Sincronizar empresas
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
            title: '¡Usuario actualizado correctamente!',
            redirectTo: route('users.index')
        );
    }

    public function getTabErrors(): array
    {
        $errors = collect($this->getErrorBag()->toArray())->keys();

        return [
            'user' => $errors->filter(fn ($k) => str_starts_with($k, 'userForm.'))->count(),
            'companies' => $errors->filter(fn ($k) => str_starts_with($k, 'companies'))->count(),
            'address' => $errors->filter(fn ($k) => str_starts_with($k, 'address.'))->count(),
            'roles' => $errors->filter(fn ($k) => str_starts_with($k, 'roles.'))->count(),
        ];
    }

    public function addPhone(): void
    {
        $this->userForm->addPhone();
    }

    public function removePhone(int $index): void
    {
        $this->userForm->removePhone($index);
    }

    public function render()
    {

        return view('livewire.users.update');
    }
}
