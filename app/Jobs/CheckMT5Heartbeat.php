<?php

namespace App\Jobs;

use App\Models\MT5Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckMT5Heartbeat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   public function handle(): void
{
    $timeout = (int) env('MT5_HEARTBEAT_TIMEOUT', 120);

    $expiredAccounts = MT5Account::whereNotNull('last_heartbeat')
        ->where('last_heartbeat', '<', now()->subSeconds($timeout))
        ->get();

    foreach ($expiredAccounts as $account) {
        $account->update([
            'is_connected' => false,
        ]);

        Log::warning('EA déconnecté par timeout heartbeat', [
            'account_id'     => $account->id,
            'account_number' => $account->account_number,
            'server'         => $account->server,
        ]);
    }
}

}
