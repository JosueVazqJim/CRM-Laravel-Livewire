<?php

namespace App\Livewire\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    protected static function bootHasSlug()
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = $model->generateUniqueSlug();
            }
        });

        static::updating(function ($model) {
            // Solo regenerar slug si cambió el nombre
            if ($model->isDirty('first_name') || $model->isDirty('paternal_surname') || $model->isDirty('maternal_surname')) {
                $model->slug = $model->generateUniqueSlug();
            }
        });
    }

    protected function generateUniqueSlug()
    {
        $baseSlug = Str::slug($this->first_name.'-'.$this->paternal_surname.'-'.$this->maternal_surname);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->where('slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
