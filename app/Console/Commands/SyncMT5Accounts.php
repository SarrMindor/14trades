<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MT5Service;
use App\Models\MT5Account;

class SyncMT5Accounts extends Command
{
    protected $signature = 'mt5:sync {--account= : Account number to sync} {--all : Sync all accounts}';
    protected $description = 'Synchronize MT5 accounts with real data';

    protected $mt5Service;

    public function __construct(MT5Service $mt5Service)
    {
        parent::__construct();
        $this->mt5Service = $mt5Service;
    }

    public function handle()
    {
        if ($this->option('account')) {
            $this->syncSingleAccount($this->option('account'));
        } elseif ($this->option('all')) {
            $this->syncAllAccounts();
        } else {
            $this->syncActiveAccounts();
        }

        $this->info('Synchronization completed successfully.');
    }

    private function syncSingleAccount($accountNumber)
    {
        $account = MT5Account::where('account_number', $accountNumber)->first();

        if (!$account) {
            $this->error("Account {$accountNumber} not found.");
            return;
        }

        $this->info("Syncing account: {$accountNumber}");

        $result = $this->mt5Service->getAccountBalance($accountNumber);

        if (!isset($result['error'])) {
            $account->update([
                'balance' => $result['balance'] ?? $account->balance,
                'equity' => $result['equity'] ?? $account->equity,
                'margin' => $result['margin'] ?? $account->margin,
                'free_margin' => $result['free_margin'] ?? $account->free_margin,
                'last_sync' => now(),
            ]);

            $this->info("✓ Account {$accountNumber} synced successfully.");
        } else {
            $this->error("✗ Failed to sync account {$accountNumber}: " . ($result['message'] ?? 'Unknown error'));
        }
    }

    private function syncAllAccounts()
    {
        $accounts = MT5Account::all();
        $total = $accounts->count();
        $synced = 0;
        $failed = 0;

        $this->info("Syncing all accounts ({$total} total)...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($accounts as $account) {
            $result = $this->mt5Service->getAccountBalance($account->account_number);

            if (!isset($result['error'])) {
                $account->update([
                    'balance' => $result['balance'] ?? $account->balance,
                    'equity' => $result['equity'] ?? $account->equity,
                    'margin' => $result['margin'] ?? $account->margin,
                    'free_margin' => $result['free_margin'] ?? $account->free_margin,
                    'last_sync' => now(),
                ]);
                $synced++;
            } else {
                $failed++;
                $this->error("Failed to sync account {$account->account_number}");
            }

            $bar->advance();
            sleep(1); // Pour éviter de surcharger l'API
        }

        $bar->finish();
        $this->newLine();

        $this->info("Synchronization summary:");
        $this->info("✓ Synced: {$synced}");
        $this->info("✗ Failed: {$failed}");
    }

    private function syncActiveAccounts()
    {
        $accounts = MT5Account::where('status', 'active')->get();
        $total = $accounts->count();

        $this->info("Syncing active accounts ({$total} total)...");

        foreach ($accounts as $account) {
            $this->syncSingleAccount($account->account_number);
            sleep(1); // Pause entre les requêtes
        }
    }
}
