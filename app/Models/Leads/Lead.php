<?php

namespace App\Models\Leads;

use App\Models\Companies\Company;
use App\Models\States\State;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'company_id', 'user_id', 'billing_id', 'name_company',
        'state_id', 'urgency_level', 'budget', 'undermine_channel',
        'classification', 'funnel_stage', 'client_progress', 'pay_status',
        'after_sales', 'problem_detected', 'demo_offered', 'probability',
        'room_number', 'client_status', 'center_number', 'first_contact',
        'differentiator_explained', 'diagnosis_made', 'last_contact', 'creat_at',
    ];

    public function getRouteKeyName()
    {
        return 'id';
    }

    protected function casts(): array
    {
        return [
            'demo_offered' => 'boolean',
            'first_contact' => 'boolean',
            'differentiator_explained' => 'boolean',
            'diagnosis_made' => 'boolean',
            'last_contact' => 'datetime',
            'creat_at' => 'datetime',
        ];
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function billingInfo()
    {
        return $this->belongsTo(BillingInfo::class, 'billing_id')->withDefault();
    }

    public function contactPeople()
    {
        return $this->hasMany(ContactPerson::class)->orderBy('id');
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function userAccess()
    {
        return $this->hasMany(LeadUserAccess::class);
    }

    // Primer usuario al que se compartió (orden de inserción)
    public function firstSharedAccess()
    {
        return $this->hasOne(LeadUserAccess::class)->oldest('id'); // o ->oldest('created_at')
    }

    public function notes()
    {
        return $this->hasMany(LeadNote::class);
    }

    /**
     * Usuario a mostrar en el listado:
     * - Si el lead lo creó system_meta y ya fue compartido, el primer usuario compartido.
     * - En cualquier otro caso, el usuario creador.
     */
    public function getDisplayUserAttribute(): ?User
    {
        if ($this->user?->first_name === 'system_meta') {
            return $this->firstSharedAccess?->user ?? $this->user;
        }

        return $this->user;
    }

    public function getClosingProbabilityAttribute(): int
    {
        $weights = [
            'demo_offered' => 25,
            'first_contact' => 15,
            'differentiator_explained' => 20,
            'diagnosis_made' => 25,
            'problem_detected' => 15,
        ];

        $score = 0;
        foreach ($weights as $field => $weight) {
            if ($this->$field) {
                $score += $weight;
            }
        }

        return $score;
    }
}
