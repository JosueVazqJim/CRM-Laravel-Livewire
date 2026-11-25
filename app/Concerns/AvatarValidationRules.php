<?php

namespace App\Concerns;

trait AvatarValidationRules
{
    /**
     * Get the validation rules for avatar uploads.
     *
     * @param  bool  $required  Si el avatar es obligatorio
     * @return array<string, mixed>
     */
    protected function avatarRules(bool $required = false): array
    {
        $rules = [
            'nullable',
            'image',
            'max:20480', // 20 MB
            'mimes:jpeg,png,jpg,gif,webp',
            'dimensions:min_width=1,min_height=1,max_width=50000,max_height=50000',
        ];

        if ($required) {
            $rules = array_merge(['required'], $rules);
        }

        return $rules;
    }

    /**
     * Get custom messages for avatar validation.
     *
     * @return array<string, string>
     */
    //    protected function avatarMessages(): array
    //    {
    //        return [
    //            'avatar.required' => 'La foto de perfil es obligatoria.',
    //            'avatar.image' => 'El archivo debe ser una imagen válida.',
    //            'avatar.max' => 'La imagen no debe pesar más de 1MB.',
    //            'avatar.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif o webp.',
    //            'avatar.dimensions' => 'La imagen debe tener entre 100x100 y 1024x1024 píxeles.',
    //        ];
    //    }

    /**
     * Get all avatar validation rules combined.
     *
     * @return array<string, mixed>
     */
    protected function fullAvatarRules(bool $required = false): array
    {
        return $this->avatarRules($required);
    }
}
