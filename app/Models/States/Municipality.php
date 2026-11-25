<?php

namespace App\Models\States;

use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    protected $fillable = ['state_id', 'name'];

    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
