<?php

namespace App\Models\Leads;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'state', 'municipality', 'colony',
        'street', 'exterior_number', 'interior_number', 'postal_code',
    ];

    public function billingInfo()
    {
        return $this->hasOne(BillingInfo::class, 'address_id');
    }
}
