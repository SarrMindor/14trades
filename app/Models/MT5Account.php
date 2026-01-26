<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MT5Account extends Model
{
    use HasFactory;

    protected $table = 'mt5_accounts';

    protected $fillable = [
        'user_id',
        'account_number',
        'status',
        'balance',
        'equity',
        'margin',
        'free_margin',
        'leverage',
        'currency',
        'last_sync',
        'notes',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'equity' => 'decimal:2',
        'margin' => 'decimal:2',
        'free_margin' => 'decimal:2',
        'last_sync' => 'datetime',
    ];

    // Relation avec l'utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation avec les trades (si tu as une table trades)
    public function trades()
    {
        return $this->hasMany(Trade::class, 'account_number', 'account_number');
    }

    // Méthodes pratiques
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function getMarginLevelAttribute()
    {
        if ($this->margin == 0) {
            return 0;
        }
        return ($this->equity / $this->margin) * 100;
    }
}
