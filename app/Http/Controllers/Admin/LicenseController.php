<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LicenseController extends Controller
{
    // Page principale des licences
    public function index()
    {
        $licenses = License::with('user')
            ->orderBy('expires_at', 'desc')
            ->paginate(20);

        $totalLicenses = License::count();
        $activeLicenses = License::where('is_active', true)
            ->where('expires_at', '>', now())
            ->count();
        $expiredLicenses = License::where('expires_at', '<', now())->count();
        $todayValidations = License::whereDate('last_validation', today())->count();

        // Lire les logs API
        $apiLogs = [];
        if (file_exists(storage_path('logs/license_api.log'))) {
            $apiLogs = array_reverse(file(storage_path('logs/license_api.log'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
            $apiLogs = array_slice($apiLogs, 0, 50);
        }

        $clients = User::where('role', 'client')->get();

        return view('admin.licenses.index', compact(
            'licenses',
            'totalLicenses',
            'activeLicenses',
            'expiredLicenses',
            'todayValidations',
            'apiLogs',
            'clients'
        ));
    }

    // Générer une nouvelle licence
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'mt5_account' => 'required|string|unique:licenses,mt5_account',
            'server' => 'required|string',
            'server_custom' => 'nullable|string',
            'hwid' => 'required|string',
            'plan' => 'required|in:basic,normal,elite',
            'duration' => 'required|in:1,3,6,12,lifetime',
            'notes' => 'nullable|string',
        ]);

        $server = $validated['server'] === 'custom'
            ? $validated['server_custom']
            : $validated['server'];

        // Calculer la date d'expiration
        if ($validated['duration'] === 'lifetime') {
            $expiresAt = now()->addYears(100);
        } else {
            $expiresAt = now()->addMonths($validated['duration']);
        }

        $license = License::create([
            'user_id' => $validated['user_id'],
            'mt5_account' => $validated['mt5_account'],
            'server' => $server,
            'hwid' => $validated['hwid'],
            'plan' => $validated['plan'],
            'expires_at' => $expiresAt,
            'is_active' => true,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Mettre à jour le fichier licenses.txt
        $this->updateLicenseFile();

        return redirect()->route('admin.licenses.index')
            ->with('success', 'Licence générée avec succès !');
    }

    // Mettre à jour une licence
    public function update(Request $request, License $license)
    {
        $validated = $request->validate([
            'mt5_account' => 'required|string|unique:licenses,mt5_account,' . $license->id,
            'server' => 'required|string',
            'hwid' => 'required|string',
            'plan' => 'required|in:basic,normal,elite',
            'expires_at' => 'required|date',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $license->update($validated);

        // Mettre à jour le fichier licenses.txt
        $this->updateLicenseFile();

        return redirect()->route('admin.licenses.index')
            ->with('success', 'Licence mise à jour avec succès !');
    }

    // Génération en masse
    public function batch(Request $request)
    {
        $validated = $request->validate([
            'csv_data' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
            'generate_txt' => 'boolean',
        ]);

        $lines = explode("\n", $validated['csv_data']);
        $generated = 0;
        $errors = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $parts = explode(',', $line);
            if (count($parts) < 4) {
                $errors[] = "Ligne " . ($index + 1) . ": Format invalide";
                continue;
            }

            list($mt5_account, $server, $plan, $duration) = $parts;

            if (!in_array($plan, ['basic', 'normal', 'elite'])) {
                $errors[] = "Ligne " . ($index + 1) . ": Plan invalide";
                continue;
            }

            if (!is_numeric($duration) || $duration < 1) {
                $errors[] = "Ligne " . ($index + 1) . ": Durée invalide";
                continue;
            }

            try {
                License::create([
                    'user_id' => $validated['user_id'],
                    'mt5_account' => trim($mt5_account),
                    'server' => trim($server),
                    'hwid' => 'HWID-' . strtoupper(bin2hex(random_bytes(6))),
                    'plan' => trim($plan),
                    'expires_at' => now()->addMonths((int)$duration),
                    'is_active' => true,
                ]);
                $generated++;
            } catch (\Exception $e) {
                $errors[] = "Ligne " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        if ($request->boolean('generate_txt')) {
            $this->updateLicenseFile();
        }

        $message = "{$generated} licences générées avec succès !";
        if (!empty($errors)) {
            $message .= " Erreurs: " . implode(', ', $errors);
        }

        return redirect()->route('admin.licenses.index')
            ->with($errors ? 'warning' : 'success', $message);
    }

    // Désactiver une licence
    public function deactivate(License $license)
    {
        $license->update(['is_active' => false]);
        $this->updateLicenseFile();

        return response()->json(['success' => true]);
    }

    // Exporter en TXT
    public function export()
    {
        $licenses = License::where('is_active', true)
            ->where('expires_at', '>', now())
            ->get();

        $content = "account;server;hwid;plan;expiry\n";
        foreach ($licenses as $license) {
            $content .= $license->getLicenseString() . "\n";
        }

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="licenses.txt"');
    }

    // Mettre à jour le fichier licenses.txt
    private function updateLicenseFile(): void
    {
        $licenses = License::where('is_active', true)
            ->where('expires_at', '>', now())
            ->get();

        $content = "";
        foreach ($licenses as $license) {
            $content .= $license->getLicenseString() . "\n";
        }

        Storage::disk('public')->put('licenses.txt', $content);
    }

    // Voir les détails d'une licence
    public function show(License $license)
    {
        return view('admin.licenses.show', compact('license'));
    }

    // Supprimer une licence
    public function destroy(License $license)
    {
        $license->delete();
        $this->updateLicenseFile();

        return redirect()->route('admin.licenses.index')
            ->with('success', 'Licence supprimée avec succès !');
    }
}
