<?php

namespace App\Models\Companies;

use Illuminate\Database\Eloquent\Model;

class CompanyDefaultNote extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'content',
        'sort_order',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
