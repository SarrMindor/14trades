<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class License extends Model
{
    protected $fillable = [
        'user_id',
        'mt5_account',
        'server',
        'hwid',
        'plan',
        'expires_at',
        'is_active',
        'last_validation',
        'validation_count',
        'notes',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_validation' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relation avec l'utilisateur
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Vérifier si la licence est valide
    public function isValid(): bool
    {
        return $this->is_active && $this->expires_at->isFuture();
    }

    // Obtenir le statut de la licence
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->expires_at->isPast()) {
            return 'expired';
        }

        return 'active';
    }

    // Incrémenter le compteur de validation
    public function incrementValidation(): void
    {
        $this->update([
            'last_validation' => now(),
            'validation_count' => $this->validation_count + 1
        ]);
    }

    // Obtenir la chaîne de licence pour le fichier TXT
    public function getLicenseString(): string
    {
        return sprintf(
            "%s;%s;%s;%s;%s",
            $this->mt5_account,
            $this->server,
            $this->hwid,
            strtoupper($this->plan),
            $this->expires_at->format('Y.m.d')
        );
    }

    // Obtenir le multiplicateur de lot selon le plan
    public function getLotMultiplier(): float
    {
        return match($this->plan) {
            'basic' => 1.0,
            'normal' => 1.5,
            'elite' => 2.0,
            default => 1.0,
        };
    }

    // Vérifier si la licence expire bientôt (dans 7 jours)
    public function isExpiringSoon(): bool
    {
        return $this->expires_at->diffInDays(now()) <= 7;
    }
}
