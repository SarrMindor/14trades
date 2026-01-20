<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // <-- Important

class Payment extends Model
{
    use HasFactory; // <-- Cela ajoute les fonctionnalités Eloquent

    protected $fillable = [
        'user_id',
        'transaction_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Relation avec l'utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
