<?php

namespace App\Livewire\Companies;

use App\Livewire\Traits\ClearsProperties;
use App\Models\Companies\Company;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Companies')]

class Index extends Component
{
    use ClearsProperties, WithPagination;

    public string $search = '';

    public string $name = '';

    public string $email = '';

    public string $created_at = '';

    public string $updated_at = '';

    public array $sortBy = ['column' => 'updated_at', 'direction' => 'desc'];

    public bool $showFilters = false;

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

        return $count;
    }

    // Método principal que obtiene los usuarios con filtros
    public function getCompaniesProperty(): LengthAwarePaginator
    {
        $query = Company::query();

        // BÚSQUEDA GLOBAL - Busca en múltiples campos
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
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
            ['key' => 'name', 'label' => 'Nombre', 'sortBy' => 'name'],
            ['key' => 'slug', 'label' => 'Abreviación', 'sortBy' => 'slug'],
            ['key' => 'created_at', 'label' => 'Creado',  'format' => ['date', 'd/m/Y H:i:s'], 'sortBy' => 'created_at'],
            ['key' => 'updated_at', 'label' => 'Actualizado', 'format' => ['date', 'd/m/Y H:i:s'], 'sortBy' => 'updated_at'],
            ['key' => 'active', 'label' => 'Estado', 'class' => 'text-center'],
        ];
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'companies' => $this->companies,
            'filterCount' => $this->filterCount,
        ];
    }

    public function render()
    {
        return view('livewire.companies.index');
    }
}
