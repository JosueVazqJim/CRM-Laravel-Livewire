<?php

namespace App\Models\Leads;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadUserAccess extends Model
{
    use HasFactory;

    protected $table = 'lead_user_access';

    protected $fillable = [
        'lead_id', 'user_id', 'permission_level',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
