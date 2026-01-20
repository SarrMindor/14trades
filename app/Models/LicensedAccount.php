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
}
