<?php

namespace App\Models\Companies;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyPhone extends Model
{
    /** @use HasFactory<\Database\Factories\Companies\CompanyPhoneFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'phone',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
}
