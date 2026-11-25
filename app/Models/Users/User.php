<?php

namespace App\Models\Users;

use App\Livewire\Traits\HasSlug;
use App\Models\Companies\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

// class User extends Authenticatable implements MustVerifyEmail
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\Users\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    use HasRoles;
    use HasSlug;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'paternal_surname',
        'maternal_surname',
        'sex',
        'rfc',
        'email',
        'password',
        'avatar',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Obtener la ruta usando slug
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Accessor para mantener compatibilidad con código que espera 'name'
     */
    public function getNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->paternal_surname.' '.$this->maternal_surname);
    }

    /**
     * Obtener el nombre completo
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->paternal_surname.' '.$this->maternal_surname);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if (! $this->avatar) {
            return asset('images/empty-user.jpg');
        }

        // Si la ruta ya empieza con 'http', es una URL completa
        if (str_starts_with($this->avatar, 'http')) {
            return $this->avatar;
        }

        // Si la ruta es de storage
        if (str_starts_with($this->avatar, 'avatars/')) {
            return asset('storage/'.$this->avatar);
        }

        // Si es una imagen en public
        return asset($this->avatar);
    }

    public function phones()
    {
        return $this->hasMany(UserPhone::class);
    }

    public function address()
    {
        return $this->hasOne(UserAddress::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class,
            'company_user',
            'user_id',
            'company_id')
            ->withTimestamps();
    }
}
