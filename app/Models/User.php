<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role', // Ajoute role ici
        'is_approved',
        'subscription_status',
        'plan',
        'subscription_ends_at',
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
            'is_approved' => 'boolean',
            'subscription_ends_at' => 'datetime',
        ];
    }

    // Méthode pour vérifier les rôles
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    // Méthode pour vérifier si l'utilisateur est admin
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    // Méthode pour vérifier si l'utilisateur est client
    public function isClient()
    {
        return $this->role === 'client';
    }

    public function mt5Accounts()
    {
        return $this->hasMany(MT5Account::class);
    }

    public function trades()
    {
        return $this->hasMany(Trade::class);
    }
    public function licensedAccounts()
    {
        return $this->hasMany(LicensedAccount::class);
    }
    // App\Models\User.php
    public function getVerifiedAmountAttribute()
    {
        return $this->payments()
            ->where('status', 'approved')
            ->sum('amount');
    }

}
