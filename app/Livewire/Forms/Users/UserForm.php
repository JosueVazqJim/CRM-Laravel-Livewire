<?php

namespace App\Livewire\Forms\Users;

use App\Concerns\AvatarValidationRules;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Users\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Form;

class UserForm extends Form
{
    use AvatarValidationRules;
    use PasswordValidationRules;
    use ProfileValidationRules;

    public ?User $userModel = null; // Añadimos esto para referencia

    public string $first_name = '';

    public string $paternal_surname = '';

    public string $maternal_surname = '';

    public string $sex = '';

    public string $rfc = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public $avatar = null;

    public array $phones = [
        ['phone' => '', 'type' => ''],
    ];

    public function rules(): array
    {
        // Si estamos en edición, tenemos $this->userModel con el User. Extraemos su id.
        $userId = $this->userModel?->id ?? null;

        // Determinar si avatar es un archivo subido (Livewire TemporaryUploadedFile / UploadedFile)
        $isAvatarUpload = $this->avatar && is_object($this->avatar) && method_exists($this->avatar, 'store');

        // Solo aplicar las reglas de imagen/dimensions cuando realmente venga un archivo subido.
        $avatarRule = $isAvatarUpload ? $this->avatarRules() : ['nullable'];

        $rules = [
            // Pasamos el id para que profileRules genere las reglas 'unique' con ignore($userId)
            ...$this->profileRules($userId),
            'avatar' => $avatarRule,
        ];

        // La contraseña es requerida solo en creación, opcional en edición
        if (! $this->userModel) {
            $rules['password'] = $this->passwordRules();
        } else {
            $rules['password'] = 'nullable|min:8'; // Opcional en edición
        }

        return $rules;
    }

    public function setUser(User $user): void
    {
        $this->userModel = $user;

        $this->first_name = $user->first_name;
        // Columnas nullable en BD: normalizamos a '' porque las propiedades son string no-nullable
        $this->paternal_surname = $user->paternal_surname ?? '';
        $this->maternal_surname = $user->maternal_surname ?? '';
        $this->sex = $user->sex;
        $this->rfc = $user->rfc ?? '';
        $this->email = $user->email;
        $this->avatar = $user->avatar; // Cargamos el path del avatar, no el archivo
        $this->password = ''; // No cargamos la contraseña por seguridad, el usuario deberá ingresar una nueva si desea cambiarla

        // Cargar teléfonos si existen
        if ($user->phones->isNotEmpty()) {
            $this->phones = $user->phones->map(fn ($phone) => [
                'phone' => $phone->phone,
                'type' => $phone->type,
            ])->toArray();
        }
    }

    public function store(): User
    {
        $avatarPath = $this->avatar
            ? $this->avatar->store('avatars', 'public')
            : 'images/empty-user.jpg';

        $user = User::create([
            'first_name' => $this->first_name,
            'paternal_surname' => $this->paternal_surname,
            'maternal_surname' => $this->maternal_surname,
            'sex' => $this->sex,
            'rfc' => $this->rfc !== '' ? $this->rfc : null,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'avatar' => $avatarPath,
        ]);

        $this->syncPhones($user);

        return $user;
    }

    public function update(): User
    {
        // Determinar la ruta del avatar:
        // - si es un archivo subido (tiene method store), lo guardamos
        // - si es una string no vacía (ruta existente), la reutilizamos
        // - si no hay nada, usamos la imagen por defecto
        if ($this->avatar && is_object($this->avatar) && method_exists($this->avatar, 'store')) {
            // Livewire TemporaryUploadedFile o UploadedFile
            $avatarPath = $this->avatar->store('avatars', 'public');
        } elseif (is_string($this->avatar) && ! empty($this->avatar)) {
            // Ya es una ruta (p. ej. 'avatars/xxx.jpg' o URL)
            $avatarPath = $this->avatar;
        } else {
            $avatarPath = 'images/empty-user.jpg';
        }

        $data = [
            'first_name' => $this->first_name,
            'paternal_surname' => $this->paternal_surname,
            'maternal_surname' => $this->maternal_surname,
            'sex' => $this->sex,
            'rfc' => $this->rfc !== '' ? $this->rfc : null,
            'email' => $this->email,
            'avatar' => $avatarPath,
        ];

        // Solo actualizar la contraseña si el usuario ingresó una nueva
        if (! empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        $this->userModel->update($data);

        // Sincronizar teléfonos (eliminar existentes y crear nuevos)
        $this->syncPhones($this->userModel, true);

        return $this->userModel->fresh();
    }

    protected function syncPhones(User $user, bool $deleteExisting = false): void
    {
        if ($deleteExisting) {
            $user->phones()->delete();
        }

        if (! empty($this->phones)) {
            $validPhones = array_filter($this->phones, fn ($phone) => ! empty($phone['phone']) && ! empty($phone['type'])
            );

            if (! empty($validPhones)) {
                $user->phones()->createMany($validPhones);
            }
        }
    }

    public function addPhone(): void
    {
        $this->phones[] = [
            'phone' => '',
            'type' => '',
        ];
    }

    public function removePhone(int $index): void
    {
        unset($this->phones[$index]);

        // Reindexar array para evitar problemas con Livewire DOM diff
        $this->phones = array_values($this->phones);
    }
}
