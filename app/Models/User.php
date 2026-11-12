<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'tipe_pemohon',
        'nik',
        'nib',
        'npwp',
        'phone',
        'instansi',
        'alamat',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return ($this->role === 'admin' || str_ends_with($this->email, '@pemanfaatan.jabarprov.go.id')) && $this->is_active;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPemohon(): bool
    {
        return $this->role === 'peminat';
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
