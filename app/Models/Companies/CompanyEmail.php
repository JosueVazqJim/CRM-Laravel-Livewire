<?php

namespace App\Models\Companies;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyEmail extends Model
{
    /** @use HasFactory<\Database\Factories\Companies\CompanyEmailFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'email',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
}
