<?php

namespace App\Models\Users;

use App\Models\States\Municipality;
use App\Models\States\State;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    /** @use HasFactory<\Database\Factories\Users\UserAddressFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'state',
        'municipality',
        'colony',
        'street',
        'exterior_number',
        'interior_number',
        'postal_code',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
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
