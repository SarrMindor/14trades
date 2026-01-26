<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicensedAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_id',
        'login',
        'password',
        'server',
        'is_active',
        'api_token',
        'hwid'
    ];

    // Relation inverse vers l’utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Si tu veux les logs d’accès
    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class);
    }

    public function trades()
    {
        return $this->hasMany(Trade::class);
    }

// Décryptage du mot de passe
    public function getPasswordAttribute($value)
    {
        return \Illuminate\Support\Facades\Crypt::decryptString($value);
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = \Illuminate\Support\Facades\Crypt::encryptString($value);
    }
}
