<?php

namespace App\Models\Leads;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadActivity extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'lead_id',
        'user_id',
        'date_action',
        'type_action',
        'is_completed',
        'scheduled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'date_action' => 'datetime',
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
            'is_completed' => 'boolean',
        ];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes útiles para filtrar actividades
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('scheduled_at')
                ->orWhere('scheduled_at', '<=', now());
        });
    }

    // Método para verificar si es una actividad futura
    public function isFuture(): bool
    {
        return ! $this->is_completed && $this->scheduled_at && $this->scheduled_at > now();
    }

    // Método para verificar si está vencida (pendiente y scheduled_at ya pasó)
    public function isOverdue(): bool
    {
        return ! $this->is_completed && $this->scheduled_at && $this->scheduled_at < now();
    }

    // Método para marcar como completada
    public function markAsCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    // Método para reprogramar
    public function reschedule(\DateTime $newDate): void
    {
        $this->update([
            'scheduled_at' => $newDate,
            'is_completed' => false,
            'completed_at' => null,
        ]);
    }
}
