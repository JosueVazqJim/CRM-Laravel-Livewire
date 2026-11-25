<?php

namespace App\Models\Leads;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelContact extends Model
{
    use HasFactory;

    protected $table = 'channel_contact';

    public $timestamps = false;

    protected $fillable = [
        'lead_contact_id', 'type', 'channel',
    ];

    public function contactPerson()
    {
        return $this->belongsTo(ContactPerson::class, 'lead_contact_id');
    }
}
