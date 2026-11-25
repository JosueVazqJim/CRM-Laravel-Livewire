<?php

namespace App\Livewire\Forms\Companies;

use AllowDynamicProperties;
use App\Concerns\AvatarValidationRules;
use App\Concerns\CompanyValidationRules;
use App\Models\Companies\Company;
use Livewire\Form;

#[AllowDynamicProperties]
class CompanyForm extends Form
{
    use AvatarValidationRules;
    use CompanyValidationRules;

    public ?Company $companyModel = null; // Añadimos esto para referencia

    public string $name = '';

    public string $slug = '';

    public string $website = '';

    public string $description = '';

    public string $theme = '';

    public $avatar = null;

    public array $phones = [
        ['phone' => ''],
    ];

    public array $emails = [
        ['email' => ''],
    ];

    public function rules(): array
    {
        // Si estamos en edición, tenemos $this->userModel con el User. Extraemos su id.
        $companyId = $this->companyModel?->id ?? null;

        // Determinar si avatar es un archivo subido (Livewire TemporaryUploadedFile / UploadedFile)
        $isAvatarUpload = $this->avatar && is_object($this->avatar) && method_exists($this->avatar, 'store');

        // Solo aplicar las reglas de imagen/dimensions cuando realmente venga un archivo subido.
        $avatarRule = $isAvatarUpload ? $this->avatarRules() : ['nullable'];

        return [
            // Pasamos el id para que profileRules genere las reglas 'unique' con ignore($userId)
            ...$this->companyRules($companyId),
            'avatar' => $avatarRule,
        ];
    }

    public function setCompany(Company $company): void
    {
        $this->companyModel = $company;

        $this->name = $company->name;
        $this->slug = $company->slug;
        $this->website = $company->website;
        $this->description = $company->description;
        $this->theme = $company->theme;
        $this->avatar = $company->avatar;

        // Cargar teléfonos si existen
        if ($company->companyPhones->isNotEmpty()) {
            $this->phones = $company->companyPhones->map(fn ($phone) => [
                'phone' => $phone->phone,
            ])->toArray();
        }

        if ($company->companyEmails->isNotEmpty()) {
            $this->emails = $company->companyEmails->map(fn ($email) => [
                'email' => $email->email,
            ])->toArray();
        }
    }

    public function store(): Company
    {
        $avatarPath = $this->avatar
            ? $this->avatar->store('logos', 'public')
            : 'images/no-image.jpg';

        $company = Company::create([
            'name' => $this->name,
            'slug' => $this->slug,
            'website' => $this->website,
            'description' => $this->description,
            'theme' => $this->theme,
            'avatar' => $avatarPath,
        ]);

        $this->syncPhones($company);

        $this->syncEmails($company);

        return $company;
    }

    public function update(): Company
    {
        // Determinar la ruta del avatar:
        // - si es un archivo subido (tiene method store), lo guardamos
        // - si es una string no vacía (ruta existente), la reutilizamos
        // - si no hay nada, usamos la imagen por defecto
        if ($this->avatar && is_object($this->avatar) && method_exists($this->avatar, 'store')) {
            // Livewire TemporaryUploadedFile o UploadedFile
            $avatarPath = $this->avatar->store('logos', 'public');
        } elseif (is_string($this->avatar) && ! empty($this->avatar)) {
            // Ya es una ruta (p. ej. 'avatars/xxx.jpg' o URL)
            $avatarPath = $this->avatar;
        } else {
            $avatarPath = 'images/no-image.jpg';
        }

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'website' => $this->website,
            'description' => $this->description,
            'theme' => $this->theme,
            'avatar' => $avatarPath,
        ];

        $this->companyModel->update($data);

        // Sincronizar teléfonos (eliminar existentes y crear nuevos)
        $this->syncPhones($this->companyModel, true);

        $this->syncEmails($this->companyModel, true);

        return $this->companyModel->fresh();
    }

    protected function syncPhones(Company $company, bool $deleteExisting = false): void
    {
        if ($deleteExisting) {
            $company->companyPhones()->delete();
        }

        if (! empty($this->phones)) {
            $validPhones = array_filter($this->phones, fn ($phone) => ! empty($phone['phone'])
            );

            if (! empty($validPhones)) {
                $company->companyPhones()->createMany($validPhones);
            }
        }
    }

    protected function syncEmails(Company $company, bool $deleteExisting = false): void
    {
        if ($deleteExisting) {
            $company->companyEmails()->delete();
        }

        if (! empty($this->emails)) {
            $validEmails = array_filter($this->emails, fn ($email) => ! empty($email['email'])
            );

            if (! empty($validEmails)) {
                $company->companyEmails()->createMany($validEmails);
            }
        }
    }

    public function addPhone(): void
    {
        $this->phones[] = [
            'phone' => '',
        ];
    }

    public function removePhone(int $index): void
    {
        unset($this->phones[$index]);

        // Reindexar array para evitar problemas con Livewire DOM diff
        $this->phones = array_values($this->phones);
    }

    public function addEmail(): void
    {
        $this->emails[] = [
            'email' => '',
        ];
    }

    public function removeEmail(int $index): void
    {
        unset($this->emails[$index]);

        // Reindexar array para evitar problemas con Livewire DOM diff
        $this->emails = array_values($this->emails);
    }
}
