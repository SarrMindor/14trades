<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_number',
        'ticket',
        'symbol',
        'type',
        'volume',
        'open_price',
        'close_price',
        'stop_loss',
        'take_profit',
        'swap',
        'commission',
        'profit',
        'open_time',
        'close_time',
        'result',
        'comment',
    ];

    protected $casts = [
        'open_time' => 'datetime',
        'close_time' => 'datetime',
        'volume' => 'decimal:2',
        'open_price' => 'decimal:5',
        'close_price' => 'decimal:5',
        'stop_loss' => 'decimal:5',
        'take_profit' => 'decimal:5',
        'swap' => 'decimal:2',
        'commission' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    // Relation avec l'utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation avec le compte MT5
    public function mt5Account()
    {
        return $this->belongsTo(MT5Account::class, 'account_number', 'account_number');
    }

    // Méthodes pratiques
    public function isClosed()
    {
        return !is_null($this->close_time);
    }

    public function getDurationAttribute()
    {
        if (!$this->close_time) {
            return null;
        }
        return $this->open_time->diff($this->close_time);
    }
}
