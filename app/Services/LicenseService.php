<?php

namespace App\Services;

use App\Models\License;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class LicenseService
{
    // Générer une licence
    public function generateLicense(array $data): License
    {
        $license = License::create($data);
        $this->updateLicenseFile();

        // Envoyer un email au client
        if ($license->user && $license->user->email) {
            $this->sendLicenseEmail($license);
        }

        return $license;
    }

    // Générer des licences en masse
    public function generateBatch(array $licensesData): array
    {
        $results = [
            'success' => 0,
            'errors' => [],
        ];

        foreach ($licensesData as $index => $data) {
            try {
                $this->generateLicense($data);
                $results['success']++;
            } catch (\Exception $e) {
                $results['errors'][] = "Ligne {$index}: {$e->getMessage()}";
            }
        }

        return $results;
    }

    // Mettre à jour le fichier licenses.txt
    public function updateLicenseFile(): void
    {
        $licenses = License::where('is_active', true)
            ->where('expires_at', '>', now())
            ->get();

        $content = "account;server;hwid;plan;expiry\n";
        foreach ($licenses as $license) {
            $content .= $license->getLicenseString() . "\n";
        }

        Storage::disk('public')->put('licenses.txt', $content);
    }

    // Valider une licence via l'API
    public function validateLicense(string $account, string $server, string $hwid): array
    {
        $license = License::where('mt5_account', $account)
            ->where('server', $server)
            ->where('hwid', $hwid)
            ->where('is_active', true)
            ->first();

        if (!$license) {
            return [
                'valid' => false,
                'message' => 'License not found',
            ];
        }

        if ($license->expires_at->isPast()) {
            $license->update(['is_active' => false]);
            return [
                'valid' => false,
                'message' => 'License expired',
            ];
        }

        $license->incrementValidation();

        return [
            'valid' => true,
            'plan' => strtoupper($license->plan),
            'expires_at' => $license->expires_at->format('Y.m.d'),
            'lot_multiplier' => $license->getLotMultiplier(),
        ];
    }

    // Envoyer l'email de licence
    private function sendLicenseEmail(License $license): void
    {
        $user = $license->user;

        \Mail::send('emails.license.created', [
            'license' => $license,
            'user' => $user,
        ], function ($message) use ($user, $license) {
            $message->to($user->email)
                ->subject('Votre licence 14TRADES PRO a été créée !')
                ->attachFromStorage('public/licenses.txt');
        });
    }

    // Obtenir les statistiques des licences
    public function getLicenseStats(): array
    {
        return [
            'total' => License::count(),
            'active' => License::where('is_active', true)->where('expires_at', '>', now())->count(),
            'expired' => License::where('expires_at', '<', now())->count(),
            'expiring_soon' => License::where('is_active', true)
                ->whereBetween('expires_at', [now(), now()->addDays(7)])
                ->count(),
            'validations_today' => License::whereDate('last_validation', today())->count(),
        ];
    }
}
