<?php

namespace App\Livewire\Companies;

use App\Livewire\Forms\Companies\AddressCompanyForm;
use App\Livewire\Forms\Companies\CompanyForm;
use App\Models\Companies\Module;
use App\Models\States\Municipality;
use App\Models\States\State;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Title('Create company')]
class Create extends Component
{
    use Toast;
    use WithFileUploads;

    public CompanyForm $company;

    public AddressCompanyForm $address;

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

    public function mount(): void
    {
        $this->company = new CompanyForm($this, 'company');
        $this->address = new AddressCompanyForm($this, 'address');

        $this->modulesOptions = Module::all()->toArray();

        $this->loadStates();

        if (! empty($this->address->state)) {
            $this->loadMunicipalities($this->address->state);
        }
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

    public function save(): void
    {
        $this->validate();

        $company = $this->company->store();

        $this->address->store($company);

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
            title: '¡Empresa creada correctamente!',
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
        $this->company->addPhone();
    }

    public function removePhone(int $index): void
    {
        $this->company->removePhone($index);
    }

    public function addEmail(): void
    {
        $this->company->addEmail();
    }

    public function removeEmail(int $index): void
    {
        $this->company->removeEmail($index);
    }

    public function render()
    {
        return view('livewire.companies.create');
    }
}
