<?php

namespace App\Livewire\Companies;

use App\Livewire\Forms\Companies\AddressCompanyForm;
use App\Livewire\Forms\Companies\CompanyForm;
use App\Models\Companies\Company;
use App\Models\Companies\Module;
use App\Models\States\Municipality;
use App\Models\States\State;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Title('Update Company')]
class Update extends Component
{
    use Toast;
    use WithFileUploads;

    public CompanyForm $companyForm;

    public AddressCompanyForm $address;

    public Company $company;

    public array $modules = [];

    public $modulesOptions = [];

    public string $tab = 'company';

    public array $defaultNotes = [];

    public bool $defaultNoteModal = false;

    public array $defaultNoteForm = [
        'title' => '',
        'content' => '',
        'sort_order' => 0,
    ];

    public ?int $editingNoteIndex = null;

    public $states = [];

    public $municipalities = [];

    public $loadingMunicipalities = false;

    public function mount(Company $company): void
    {
        $this->company = $company;

        // Inicializar forms
        $this->companyForm->setCompany($company);
        $this->address->setAddress($company->companyAddress);

        // Cargar modulos asignados
        $this->modules = $company->modules->pluck('id')->toArray();
        $this->modulesOptions = Module::all()->toArray();

        // Cargar estados
        $this->loadStates();

        // Si hay dirección, cargar municipios del estado guardado
        if ($company->companyAddress() && ! empty($company->companyAddress->state)) {
            $this->loadMunicipalities($company->companyAddress->state);
        }

        // Cargar notas predeterminadas
        $this->defaultNotes = $company->defaultNotes
            ->map(fn ($n) => [
                'title' => $n->title,
                'content' => $n->content,
                'sort_order' => $n->sort_order,
            ])->toArray();
    }

    protected function rules(): array
    {
        return [
            'modules' => ['nullable', 'array'],
            'modules.*' => ['exists:companies,id'],
        ];
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

    public function update(): void
    {
        $this->validate();

        // Actualizar usuario
        $company = $this->companyForm->update();

        // Actualizar o crear dirección
        if ($this->company->companyAddress) {
            $this->address->update();
        } else {
            $this->address->update($company);
        }

        // Sincronizar empresas
        $company->modules()->sync($this->modules);

        // Sincronizar notas predeterminadas
        $company->defaultNotes()->delete();
        foreach ($this->defaultNotes as $i => $note) {
            $company->defaultNotes()->create([
                'title' => $note['title'],
                'content' => $note['content'],
                'sort_order' => $i,
            ]);
        }

        $this->success(
            title: '¡Empresa actualizada correctamente!',
            redirectTo: route('companies.index')
        );
    }

    public function openNewDefaultNote(): void
    {
        $this->defaultNoteForm = ['title' => '', 'content' => '', 'sort_order' => 0];
        $this->editingNoteIndex = null;
        $this->defaultNoteModal = true;
    }

    public function openEditDefaultNote(int $index): void
    {
        $this->defaultNoteForm = $this->defaultNotes[$index];
        $this->editingNoteIndex = $index;
        $this->defaultNoteModal = true;
    }

    public function saveDefaultNote(): void
    {
        $this->validate([
            'defaultNoteForm.title' => 'required|string|max:100',
            'defaultNoteForm.content' => 'required|string|max:5000',
        ]);

        if ($this->editingNoteIndex !== null) {
            $this->defaultNotes[$this->editingNoteIndex] = $this->defaultNoteForm;
        } else {
            $this->defaultNoteForm['sort_order'] = count($this->defaultNotes);
            $this->defaultNotes[] = $this->defaultNoteForm;
        }

        $this->defaultNoteModal = false;
        $this->editingNoteIndex = null;
    }

    public function removeDefaultNote(int $index): void
    {
        unset($this->defaultNotes[$index]);
        $this->defaultNotes = array_values($this->defaultNotes);
        foreach ($this->defaultNotes as $i => $note) {
            $this->defaultNotes[$i]['sort_order'] = $i;
        }
    }

    public function moveDefaultNoteUp(int $index): void
    {
        if ($index === 0) {
            return;
        }
        [$this->defaultNotes[$index - 1], $this->defaultNotes[$index]] =
            [$this->defaultNotes[$index], $this->defaultNotes[$index - 1]];
        $this->defaultNotes = array_values($this->defaultNotes);
    }

    public function moveDefaultNoteDown(int $index): void
    {
        if ($index >= count($this->defaultNotes) - 1) {
            return;
        }
        [$this->defaultNotes[$index], $this->defaultNotes[$index + 1]] =
            [$this->defaultNotes[$index + 1], $this->defaultNotes[$index]];
        $this->defaultNotes = array_values($this->defaultNotes);
    }

    public function getTabErrors(): array
    {
        $errors = collect($this->getErrorBag()->toArray())->keys();

        return [
            'company' => $errors->filter(fn ($k) => str_starts_with($k, 'company.'))->count(),
            'modules' => $errors->filter(fn ($k) => str_starts_with($k, 'modules'))->count(),
            'address' => $errors->filter(fn ($k) => str_starts_with($k, 'address.'))->count(),
            'default_notes' => $errors->filter(fn ($k) => str_starts_with($k, 'defaultNoteForm'))->count(),
        ];
    }

    public function addPhone(): void
    {
        $this->companyForm->addPhone();
    }

    public function removePhone(int $index): void
    {
        $this->companyForm->removePhone($index);
    }

    public function addEmail(): void
    {
        $this->companyForm->addEmail();
    }

    public function removeEmail(int $index): void
    {
        $this->companyForm->removeEmail($index);
    }

    public function render()
    {
        return view('livewire.companies.update');
    }
}
