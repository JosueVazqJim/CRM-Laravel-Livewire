<?php

namespace App\Models\Companies;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    /** @use HasFactory<\Database\Factories\Companies\ModuleFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function companies()
    {
        return $this->belongsToMany(Company::class,
            'company_module',
            'module_id',
            'company_id')->withTimestamps();
    }
}
