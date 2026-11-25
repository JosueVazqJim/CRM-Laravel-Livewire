<?php

namespace App\Livewire\Users;

use App\Livewire\Traits\ClearsProperties;
use App\Models\Companies\Company;
use App\Models\Users\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Users')]

class Index extends Component
{
    use ClearsProperties, WithPagination;

    public string $search = '';

    public string $first_name = '';

    public string $paternal_surname = '';

    public string $maternal_surname = '';

    public string $email = '';

    public string $created_at = '';

    public string $updated_at = '';

    public array $companies = [];

    public array $companyOptions = [];

    public array $sortBy = ['column' => 'updated_at', 'direction' => 'desc'];

    public bool $showFilters = false;

    public function mount(): void
    {
        $this->companyOptions = Company::all()->toArray();
    }

    // IMPORTANTE: Resetear paginación cuando cambian los filtros
    public function updated($property): void
    {
        if (in_array($property, ['search', 'first_name', 'paternal_surname', 'maternal_surname', 'email', 'created_at', 'updated_at', 'companies'])) {
            $this->resetPage();
        }
    }

    // Método para contar filtros activos
    public function getFilterCountProperty(): int
    {
        $count = 0;
        if (! empty($this->created_at)) {
            $count++;
        }
        if (! empty($this->updated_at)) {
            $count++;
        }
        if (! empty($this->companies)) {
            $count++;
        }

        return $count;
    }

    // All users
    // Método principal que obtiene los usuarios con filtros
    public function getUsersProperty(): LengthAwarePaginator
    {
        $query = User::query();

        // BÚSQUEDA GLOBAL - Busca en múltiples campos
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('paternal_surname', 'like', '%'.$this->search.'%')
                    ->orWhere('maternal_surname', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');

                // Si el search parece un número, buscar por ID
                if (is_numeric($this->search)) {
                    $q->orWhere('id', $this->search);
                }
            });
        }

        // Aplicar filtros SOLO si tienen valor
        if (! empty($this->created_at)) {
            $query->whereDate('created_at', 'like', '%'.$this->created_at.'%');
        }

        if (! empty($this->updated_at)) {
            $query->whereDate('updated_at', 'like', '%'.$this->updated_at.'%');
        }

        if (! empty($this->companies)) {
            if (method_exists(User::class, 'companies')) {
                $companyIds = $this->companies; // capturar fuera del closure
                $query->whereHas('companies', function ($q) use ($companyIds) {
                    // calificar la columna para evitar ambigüedad
                    $q->whereIn('companies.id', $companyIds);
                });
            } else {
                $query->whereIn('company_id', $this->companies);
            }
        }

        // Aplicar ordenamiento
        return $query
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(10);
    }

    public function headers(): array
    {
        return [
            //            ['key' => 'avatar', 'label' => '', 'class' => 'w-14', 'sortable' => false],
            //            ['key' => 'id', 'label' => '#'],
            ['key' => 'avatar', 'label' => '', 'class' => 'w-14', 'sortable' => false],
            ['key' => 'first_name', 'label' => 'Nombre', 'sortBy' => 'first_name'],
            ['key' => 'paternal_surname', 'label' => 'Apellido paterno', 'sortBy' => 'paternal_surname'],
            ['key' => 'maternal_surname', 'label' => 'Apellido materno', 'sortBy' => 'maternal_surname'],
            ['key' => 'created_at', 'label' => 'Creado',  'format' => ['date', 'd/m/Y H:i:s'], 'sortBy' => 'created_at'],
            ['key' => 'updated_at', 'label' => 'Actualizado', 'format' => ['date', 'd/m/Y H:i:s'], 'sortBy' => 'updated_at'],
            ['key' => 'active', 'label' => 'Estado', 'class' => 'text-center'],
        ];
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'users' => $this->users,
            'filterCount' => $this->filterCount,
        ];
    }

    protected function filters(): array
    {
        return ['companies', 'created_at', 'updated_at'];
    }

    public function render()
    {
        return view('livewire.users.index');

    }
}
