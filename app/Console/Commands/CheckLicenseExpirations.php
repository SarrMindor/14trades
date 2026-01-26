<?php

namespace App\Console\Commands;

use App\Models\License;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckLicenseExpirations extends Command
{
    protected $signature = 'licenses:check-expirations';
    protected $description = 'Vérifie les licences expirant bientôt et désactive les expirées';

    public function handle()
    {
        $this->info('Début de la vérification des licences...');

        // 1. Désactiver les licences expirées
        $expiredCount = License::where('is_active', true)
            ->where('expires_at', '<', now())
            ->update(['is_active' => false]);

        $this->info("{$expiredCount} licences expirées désactivées.");

        // 2. Envoyer des notifications pour les licences expirant bientôt
        $expiringSoon = License::where('is_active', true)
            ->whereBetween('expires_at', [now(), now()->addDays(7)])
            ->whereNull('last_expiration_notification')
            ->with('user')
            ->get();

        $notifiedCount = 0;
        foreach ($expiringSoon as $license) {
            if ($license->user && $license->user->email) {
                $this->sendExpirationNotification($license);
                $license->update(['last_expiration_notification' => now()]);
                $notifiedCount++;
            }
        }

        $this->info("{$notifiedCount} notifications d'expiration envoyées.");

        // 3. Mettre à jour le fichier licenses.txt
        $licenseService = app(\App\Services\LicenseService::class);
        $licenseService->updateLicenseFile();

        $this->info('Vérification des licences terminée.');

        return Command::SUCCESS;
    }

    private function sendExpirationNotification(License $license): void
    {
        $user = $license->user;
        $daysLeft = $license->expires_at->diffInDays(now());

        Mail::send('emails.license.expiring', [
            'license' => $license,
            'user' => $user,
            'daysLeft' => $daysLeft,
        ], function ($message) use ($user, $daysLeft) {
            $message->to($user->email)
                ->subject("Votre licence 14TRADES PRO expire dans {$daysLeft} jours !");
        });
    }
}
