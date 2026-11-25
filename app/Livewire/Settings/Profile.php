<?php

namespace App\Livewire\Settings;

use App\Concerns\ProfileValidationSingleRules;
use App\Models\States\Municipality;
use App\Models\States\State;
use App\Models\Users\UserAddress;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Title('Profile settings')]
class Profile extends Component
{
    use ProfileValidationSingleRules;
    use Toast;
    use WithFileUploads;

    public string $first_name = '';

    public string $paternal_surname = '';

    public string $maternal_surname = '';

    public string $email = '';

    public string $sex = '';

    public string $rfc = '';

    // Puede ser string (ruta existente) o TemporaryUploadedFile (nueva subida)
    public $avatar = null;

    public array $phones = [];

    public array $address = [
        'type' => 'Casa',
        'state' => '',
        'municipality' => '',
        'colony' => '',
        'street' => '',
        'exterior_number' => '',
        'interior_number' => '',
        'postal_code' => '',
    ];

    public array $states = [];

    public array $municipalities = [];

    public function mount(): void
    {
        $user = Auth::user()->load(['phones', 'address']);

        $this->first_name = $user->first_name ?? '';
        $this->paternal_surname = $user->paternal_surname ?? '';
        $this->maternal_surname = $user->maternal_surname ?? '';
        $this->email = $user->email;
        $this->sex = $user->sex ?? '';
        $this->rfc = $user->rfc ?? '';
        $this->avatar = $user->avatar;

        $this->phones = $user->phones->isNotEmpty()
            ? $user->phones->map(fn ($p) => ['phone' => $p->phone, 'type' => $p->type ?? ''])->toArray()
            : [['phone' => '', 'type' => '']];

        if ($user->address) {
            $this->address = [
                'type' => $user->address->type ?? 'Casa',
                'state' => $user->address->state ?? '',
                'municipality' => $user->address->municipality ?? '',
                'colony' => $user->address->colony ?? '',
                'street' => $user->address->street ?? '',
                'exterior_number' => $user->address->exterior_number ?? '',
                'interior_number' => $user->address->interior_number ?? '',
                'postal_code' => $user->address->postal_code ?? '',
            ];
        }

        $this->states = State::orderBy('name')->get(['id', 'name'])->toArray();

        if (! empty($this->address['state'])) {
            $this->municipalities = Municipality::where('state_id', $this->address['state'])
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray();
        }
    }

    public function updatedAddressState($value): void
    {
        $this->address['municipality'] = '';
        $this->municipalities = ! empty($value)
            ? Municipality::where('state_id', $value)->orderBy('name')->get(['id', 'name'])->toArray()
            : [];
    }

    public function addPhone(): void
    {
        $this->phones[] = ['phone' => '', 'type' => ''];
    }

    public function removePhone(int $index): void
    {
        unset($this->phones[$index]);
        $this->phones = array_values($this->phones);
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $isNewAvatar = $this->avatar && is_object($this->avatar) && method_exists($this->avatar, 'store');

        $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'paternal_surname' => ['nullable', 'string', 'max:255'],
            'maternal_surname' => ['nullable', 'string', 'max:255'],
            'email' => $this->emailRules($user->id),
            'sex' => ['required', 'in:Masculino,Femenino,Otro'],
            'rfc' => ['nullable', 'string', 'max:13'],
            'avatar' => $isNewAvatar ? ['nullable', 'image', 'max:2048'] : ['nullable'],
            'phones' => ['nullable', 'array'],
            'phones.*.phone' => ['nullable', 'string', 'max:20'],
            'phones.*.type' => ['nullable', 'string', 'max:50'],
            'address.type' => ['nullable', 'string', 'max:50'],
            'address.state' => ['nullable', 'exists:states,id'],
            'address.municipality' => ['nullable', 'exists:municipalities,id'],
            'address.colony' => ['nullable', 'string', 'max:255'],
            'address.street' => ['nullable', 'string', 'max:255'],
            'address.exterior_number' => ['nullable', 'string', 'max:20'],
            'address.interior_number' => ['nullable', 'string', 'max:20'],
            'address.postal_code' => ['nullable', 'string', 'max:10'],
        ]);

        // Avatar: guardar si es nueva subida, conservar si es ruta existente
        if ($isNewAvatar) {
            $avatarPath = $this->avatar->store('avatars', 'public');
        } elseif (is_string($this->avatar) && ! empty($this->avatar)) {
            $avatarPath = $this->avatar;
        } else {
            $avatarPath = $user->avatar;
        }

        $user->fill([
            'first_name' => $this->first_name,
            'paternal_surname' => $this->paternal_surname,
            'maternal_surname' => $this->maternal_surname,
            'sex' => $this->sex,
            'rfc' => $this->rfc ?: null,
            'email' => $this->email,
            'avatar' => $avatarPath,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Teléfonos: reemplazar todos
        $user->phones()->delete();
        $validPhones = array_filter($this->phones, fn ($p) => ! empty($p['phone']) && ! empty($p['type']));
        if (! empty($validPhones)) {
            $user->phones()->createMany(array_values($validPhones));
        }

        // Dirección: crear o actualizar si hay datos mínimos
        if (! empty($this->address['street']) && ! empty($this->address['state'])) {
            UserAddress::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'type' => $this->address['type'] ?: 'Casa',
                    'state' => $this->address['state'],
                    'municipality' => $this->address['municipality'] ?: null,
                    'colony' => $this->address['colony'] ?: null,
                    'street' => $this->address['street'],
                    'exterior_number' => $this->address['exterior_number'] ?: '',
                    'interior_number' => $this->address['interior_number'] ?: null,
                    'postal_code' => $this->address['postal_code'] ?: '',
                ]
            );
        }

        $this->success(
            title: '¡Perfil actualizado!',
            description: 'Hemos actualizado la información de su perfil.',
            position: 'toast-top toast-end',
            timeout: 3000,
            redirectTo: route('profile.edit')
        );
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}
