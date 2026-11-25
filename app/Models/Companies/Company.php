<?php

namespace App\Models\Companies;

use App\Livewire\Traits\HasSlug;
use App\Models\Products\Product;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    /** @use HasFactory<\Database\Factories\Companies\CompanyFactory> */
    use HasFactory;

    use HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'avatar',
        'website',
        'description',
        'theme',
        'active',
    ];

    /**
     * Obtener la ruta usando slug
     */
    public function getRouteKeyName()
    {
        return 'slug';
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
        if (str_starts_with($this->avatar, 'logos/')) {
            return asset('storage/'.$this->avatar);
        }

        // Si es una imagen en public
        return asset($this->avatar);
    }

    public function getThemeRgbAttribute(): ?string
    {
        if (! $this->theme) {
            return null;
        }

        // Eliminar el '#' si existe
        $hex = ltrim($this->theme, '#');

        // Si es formato de 3 caracteres (ej: #FFF)
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        // Convertir a RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "{$r},{$g},{$b}";
    }

    public function users()
    {
        return $this->belongsToMany(User::class,
            'company_user',
            'company_id',
            'user_id')
            ->withTimestamps();
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class,
            'company_module',
            'company_id',
            'module_id')->withTimestamps();
    }

    public function companyPhones()
    {
        return $this->hasMany(CompanyPhone::class, 'company_id', 'id');
    }

    public function companyAddress()
    {
        return $this->hasOne(CompanyAddress::class, 'company_id', 'id');
    }

    public function companyEmails()
    {
        return $this->hasMany(CompanyEmail::class, 'company_id', 'id');
    }

    public function product()
    {
        return $this->hasMany(Product::class, 'company_id', 'id');
    }

    public function defaultNotes()
    {
        return $this->hasMany(CompanyDefaultNote::class)->orderBy('sort_order');
    }
}
