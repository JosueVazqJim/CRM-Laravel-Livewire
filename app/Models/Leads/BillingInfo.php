<?php

namespace App\Models\Leads;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_name', 'rfc', 'email', 'address_id',
    ];

    public function address()
    {
        return $this->belongsTo(BillingAddress::class, 'address_id')->withDefault();
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'billing_id');
    }
}
