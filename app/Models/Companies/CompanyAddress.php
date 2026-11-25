<?php

namespace App\Models\Companies;

use App\Models\States\Municipality;
use App\Models\States\State;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAddress extends Model
{
    /** @use HasFactory<\Database\Factories\Companies\CompanyAddressFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'state',
        'municipality',
        'colony',
        'street',
        'exterior_number',
        'interior_number',
        'postal_code',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function stateRelation()
    {
        return $this->belongsTo(State::class, 'state', 'id');
    }

    public function municipalityRelation()
    {
        return $this->belongsTo(Municipality::class, 'municipality', 'id');
    }
}
