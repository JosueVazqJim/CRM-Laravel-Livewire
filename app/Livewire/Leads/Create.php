<?php

namespace App\Livewire\Leads;

use App\Events\LeadAccessGranted;
use App\Models\Companies\Company;
use App\Models\Leads\BillingAddress;
use App\Models\Leads\BillingInfo;
use App\Models\Leads\ChannelContact;
use App\Models\Leads\ContactPerson;
use App\Models\Leads\Lead;
use App\Models\Leads\LeadActivity;
use App\Models\Leads\LeadUserAccess;
use App\Models\States\State;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Crear Lead')]
class Create extends Component
{
    use Toast;

    // ============================================
    // Propiedades de estado de tabs
    // ============================================
    public string $tab = 'general';

    // ============================================
    // Propiedades de datos - INFORMACIÓN GENERAL
    // ============================================
    public int $company_id = 0;

    public string $name_company = '';

    public int $state_id = 0;

    public array $companyOptions = [];

    public string $last_contact = '';

    // ============================================
    // Propiedades de datos - INFORMACIÓN COMERCIAL
    // ============================================
    public string $urgency_level = '';

    public int $budget = 0;

    public string $undermine_channel = '';

    public string $classification = 'activo';

    public string $funnel_stage = '';

    public int $probability = 0;

    // ============================================
    // Propiedades de datos - ESTADO DEL CLIENTE
    // ============================================
    public string $client_progress = 'nuevo';

    public string $client_status = 'activo';

    public string $pay_status = 'sin cierre aun';

    public string $after_sales = 'sin cierre aun';

    // ============================================
    // Propiedades de datos - DIAGNÓSTICO Y PROCESO
    // ============================================
    public string $problem_detected = '';

    public bool $demo_offered = false;

    public bool $first_contact = false;

    public bool $differentiator_explained = false;

    public bool $diagnosis_made = false;

    // ============================================
    // Propiedades de datos - FACTURACIÓN (BillingInfo)
    // ============================================
    public string $business_name = '';

    public string $rfc = '';

    public string $billing_email = '';

    // ============================================
    // Propiedades de datos - DOMICILIO (BillingAddress)
    // ============================================
    public string $address_state = '';

    public string $municipality = '';

    public string $colony = '';

    public string $street = '';

    public string $exterior_number = '';

    public string $interior_number = '';

    public string $postal_code = '';

    public array $states = [];

    public array $municipalities = [];

    public bool $loadingMunicipalities = false;

    // ============================================
    // Propiedades de datos - CONTACTOS
    // ============================================
    public array $contacts = [];

    // ============================================
    // Propiedades de datos - ACTIVIDADES
    // ============================================
    public array $activities = [];

    // ============================================
    // Propiedades de datos - ACCESO DE USUARIOS
    // ============================================
    public array $selectedUsers = [];

    public array $userOptions = [];

    public function mount(): void
    {
        // Cargar empresas del usuario autenticado
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            // Admin ve todas las empresas activas
            $companies = Company::where('active', true)->get();
        } else {
            // Vendedor solo ve sus empresas asignadas
            $companies = $user->companies()->where('active', true)->get();
        }

        // Seleccionar la primera empresa por defecto
        if (! empty($companies)) {
            $this->companyOptions = $companies->map(fn ($company) => [
                'id' => $company->id,
                'name' => $company->name,
                'avatar' => $company->avatar_url,
            ])->toArray();
            $this->company_id = $this->companyOptions[0]['id'];
        }

        // Cargar usuarios vendedores activos para compartir acceso
        $this->userOptions = \App\Models\Users\User::where('active', true)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['vendedor', 'admin']);
            })->get()->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->full_name,
            ])->toArray();

        // Cargar estados
        $this->states = State::orderBy('name')->get(['id', 'name'])->toArray();

        // Inicializar arrays dinámicos con una fila de ejemplo
        $this->contacts = [
            [
                'name_contact' => '',
                'recurring_client' => false,
                'channels' => [
                    ['type' => '', 'channel' => ''],
                ],
            ],
        ];

        $this->activities = [
            [
                'type_action' => '',
                'scheduled_at' => '',
                'is_completed' => false,
            ],
        ];
    }

    // ============================================
    // Métodos de actualización reactiva
    // ============================================

    public function updated($property): void
    {
        // Resetear página de paginación cuando cambian filtros
        // No es necesario aquí dado que es un formulario de creación
    }

    // ============================================
    // Métodos para manipular contactos dinámicos
    // ============================================

    public function addContact(): void
    {
        $this->contacts[] = [
            'name_contact' => '',
            'recurring_client' => false,
            'channels' => [
                ['type' => '', 'channel' => ''],
            ],
        ];
    }

    public function removeContact(int $index): void
    {
        unset($this->contacts[$index]);
        $this->contacts = array_values($this->contacts);
    }

    public function addChannelToContact(int $contactIndex): void
    {
        if (isset($this->contacts[$contactIndex])) {
            $this->contacts[$contactIndex]['channels'][] = ['type' => '', 'channel' => ''];
        }
    }

    public function removeChannelFromContact(int $contactIndex, int $channelIndex): void
    {
        if (isset($this->contacts[$contactIndex]['channels'][$channelIndex])) {
            unset($this->contacts[$contactIndex]['channels'][$channelIndex]);
            $this->contacts[$contactIndex]['channels'] = array_values($this->contacts[$contactIndex]['channels']);
        }
    }

    // ============================================
    // Métodos para manipular actividades dinámicas
    // ============================================

    public function addActivity(): void
    {
        $this->activities[] = [
            'type_action' => '',
            'scheduled_at' => '',
            'is_completed' => false,
        ];
    }

    public function removeActivity(int $index): void
    {
        unset($this->activities[$index]);
        $this->activities = array_values($this->activities);
    }

    // ============================================
    // Validaciones
    // ============================================

    protected function rules(): array
    {
        return [
            // Información General
            'company_id' => ['required', 'exists:companies,id'],
            'name_company' => ['nullable', 'string', 'max:255'],
            'state_id' => ['nullable', 'exists:states,id'],
            'last_contact' => ['nullable', 'string', 'max:255'],

            // Información Comercial
            'urgency_level' => ['nullable', 'string', 'max:100'],
            'budget' => ['nullable', 'integer', 'min:0'],
            'undermine_channel' => ['nullable', 'string', 'max:100'],
            'classification' => ['nullable', 'string', 'max:100'],
            'funnel_stage' => ['nullable', 'string', 'max:100'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],

            // Estado del Cliente
            'client_progress' => ['nullable', 'string', 'max:100'],
            'client_status' => ['nullable', 'string', 'max:100'],
            'pay_status' => ['nullable', 'string', 'max:100'],
            'after_sales' => ['nullable', 'string', 'max:100'],

            // Diagnóstico y Proceso
            'problem_detected' => ['nullable', 'string', 'max:5000'],
            'demo_offered' => ['boolean'],
            'first_contact' => ['boolean'],
            'differentiator_explained' => ['boolean'],
            'diagnosis_made' => ['boolean'],

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

            // Contactos
            'contacts' => ['required', 'array', 'min:1'],
            'contacts.*.name_contact' => ['required', 'string', 'max:255'],
            'contacts.*.recurring_client' => ['boolean'],
            'contacts.*.channels' => ['required', 'array', 'min:1'],
            'contacts.*.channels.*.type' => ['required', 'string', 'max:100'],
            'contacts.*.channels.*.channel' => ['required', 'string', 'max:255'],

            // Actividades
            'activities' => ['nullable', 'array'],
            'activities.*.type_action' => ['nullable', 'string', 'max:100'],
            'activities.*.scheduled_at' => ['nullable', 'date'],
            'activities.*.is_completed' => ['boolean'],

            // Acceso de Usuarios
            'selectedUsers' => ['nullable', 'array'],
            'selectedUsers.*' => ['exists:users,id'],
        ];
    }

    public function getTabErrors(): array
    {
        $errors = collect($this->getErrorBag()->toArray())->keys();

        return [
            'general' => $errors->filter(fn ($k) => in_array($k, ['company_id', 'name_company', 'state_id', 'last_contact']))->count(),
            'commercial' => $errors->filter(fn ($k) => in_array($k, ['urgency_level', 'budget', 'undermine_channel', 'classification', 'funnel_stage', 'probability']))->count(),
            'client_status' => $errors->filter(fn ($k) => in_array($k, ['client_progress', 'client_status', 'pay_status', 'after_sales']))->count(),
            'process' => $errors->filter(fn ($k) => in_array($k, ['problem_detected', 'demo_offered', 'first_contact', 'differentiator_explained', 'diagnosis_made']))->count(),
            'billing' => $errors->filter(fn ($k) => str_starts_with($k, 'business_name') || str_starts_with($k, 'rfc') || str_starts_with($k, 'billing_email') || in_array($k, ['address_state', 'municipality', 'colony', 'street', 'exterior_number', 'interior_number', 'postal_code']))->count(),
            'contacts' => $errors->filter(fn ($k) => str_starts_with($k, 'contacts'))->count(),
            'activities' => $errors->filter(fn ($k) => str_starts_with($k, 'activities'))->count(),
            'user_access' => $errors->filter(fn ($k) => str_starts_with($k, 'selectedUsers'))->count(),
        ];
    }

    // ============================================
    // Método principal de guardado
    // ============================================

    public function save(): void
    {
        // Validar todos los datos
        $this->validate();

        try {
            // Usar una transacción para garantizar consistencia
            DB::beginTransaction();

            // 1. Crear dirección de facturación
            $billingAddress = BillingAddress::create([
                'type' => 'billing',
                'state' => $this->address_state,
                'municipality' => $this->municipality,
                'colony' => $this->colony,
                'street' => $this->street,
                'exterior_number' => $this->exterior_number,
                'interior_number' => $this->interior_number,
                'postal_code' => $this->postal_code,
            ]);

            // 2. Crear información de facturación
            $billingInfo = BillingInfo::create([
                'business_name' => $this->business_name,
                'rfc' => $this->rfc,
                'email' => $this->billing_email,
                'address_id' => $billingAddress->id,
            ]);

            // 3. Crear Lead
            $lead = Lead::create([
                'company_id' => $this->company_id,
                'user_id' => auth()->id(),
                'billing_id' => $billingInfo->id,
                'name_company' => $this->name_company,
                'state_id' => $this->state_id ?: null,
                'last_contact' => $this->last_contact ?: null,
                'urgency_level' => $this->urgency_level,
                'budget' => $this->budget ?: null,
                'undermine_channel' => $this->undermine_channel,
                'classification' => $this->classification,
                'funnel_stage' => $this->funnel_stage,
                'client_progress' => $this->client_progress,
                'pay_status' => $this->pay_status,
                'after_sales' => $this->after_sales,
                'problem_detected' => $this->problem_detected,
                'demo_offered' => $this->demo_offered,
                'probability' => $this->calculateClosingProbability(),
                'client_status' => $this->client_status,
                'first_contact' => $this->first_contact,
                'differentiator_explained' => $this->differentiator_explained,
                'diagnosis_made' => $this->diagnosis_made,
                'creat_at' => now(),
            ]);

            // 4. Crear contactos y canales asociados
            foreach ($this->contacts as $contactData) {
                if (! empty($contactData['name_contact'])) {
                    $contact = ContactPerson::create([
                        'lead_id' => $lead->id,
                        'name_contact' => $contactData['name_contact'],
                        'recurring_client' => $contactData['recurring_client'] ?? false,
                        'creat_at' => now(),
                    ]);

                    // Crear canales de contacto
                    foreach ($contactData['channels'] ?? [] as $channelData) {
                        if (! empty($channelData['type']) && ! empty($channelData['channel'])) {
                            ChannelContact::create([
                                'lead_contact_id' => $contact->id,
                                'type' => $channelData['type'],
                                'channel' => $channelData['channel'],
                            ]);
                        }
                    }
                }
            }

            // 5. Crear actividades
            //            foreach ($this->activities as $activityData) {
            //                if (! empty($activityData['type_action']) && ! empty($activityData['scheduled_at'])) {
            //                    LeadActivity::create([
            //                        'lead_id' => $lead->id,
            //                        'type_action' => $activityData['type_action'],
            //                        'scheduled_at' => $activityData['scheduled_at'],
            //                        'is_completed' => $activityData['is_completed'] ?? false,
            //                        'creat_at' => now(),
            //                    ]);
            //                }
            //            }

            // 6. Crear acceso de usuarios
            if (! empty($this->selectedUsers)) {
                foreach ($this->selectedUsers as $userId) {
                    LeadUserAccess::create([
                        'lead_id' => $lead->id,
                        'user_id' => $userId,
                        'permission_level' => 'view', // Permiso por defecto
                    ]);
                }

                $users = User::whereIn('id', $this->selectedUsers)->get();
                foreach ($users as $user) {
                    // 2. Notificación en BD + broadcast en tiempo real
                    $user->notify(new \App\Notifications\LeadAccessGrantedNotification(
                        $lead,
                        auth()->user()
                    ));

                    LeadAccessGranted::dispatch($lead, auth()->user(), $user);
                }
            }

            DB::commit();

            // Éxito
            $this->success(
                title: '¡Lead creado correctamente!',
                description: 'El lead ha sido guardado exitosamente.',
                redirectTo: route('leads.index')
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error(
                title: 'Error al crear el lead',
                //                description: 'Ocurrió un error durante el intento de creación del lead. Contacte con el administrador.',
                description: $e->getMessage()
            );
        }
    }

    private function calculateClosingProbability(): int
    {
        $weights = [
            'demo_offered' => 25,
            'first_contact' => 15,
            'differentiator_explained' => 20,
            'diagnosis_made' => 25,
            'problem_detected' => 15,
        ];

        $score = 0;
        foreach ($weights as $field => $weight) {
            if ($this->$field) {
                $score += $weight;
            }
        }

        return $score;
    }

    public function render()
    {
        return view('livewire.leads.create');
    }
}
