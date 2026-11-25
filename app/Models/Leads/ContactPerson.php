<?php

namespace App\Models\Leads;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactPerson extends Model
{
    use HasFactory;

    protected $table = 'contact_people';

    public $timestamps = false;

    protected $fillable = [
        'lead_id', 'name_contact', 'recurring_client', 'creat_at',
    ];

    protected function casts(): array
    {
        return [
            'recurring_client' => 'boolean',
            'creat_at' => 'datetime',
        ];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function channels()
    {
        return $this->hasMany(ChannelContact::class, 'lead_contact_id');
    }
}
